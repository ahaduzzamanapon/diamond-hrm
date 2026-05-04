<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BiometricDevice;
use App\Models\BiometricLog;
use App\Models\Employee;
use App\Models\ExtraPresentRequest;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * ZKTeco ADMS Push Server
 * Device Cloud Server Settings (K40 Pro):
 *   Server Mode: ADMS
 *   Server Address: your-domain.com
 */
class AdmsController extends Controller
{
    // Heartbeat / check-in from device
    public function getRequest(Request $request)
    {
        $sn = $request->SN ?? $request->input('SN');
        if ($sn) {
            BiometricDevice::where('serial_number', $sn)
                ->update(['last_online' => now()]);
        }
        return response('', 200)->header('Content-Type', 'text/plain');
    }

    // Attendance push from device
    public function attendance(Request $request)
    {
        Log::info('ADMS Attendance Push', $request->all());

        $sn   = $request->SN ?? 'UNKNOWN';
        $data = $request->getContent();

        // ── BUG-005 FIX: Cache all employees by biometric_user_id to avoid N+1 ──
        $employeeMap = Employee::whereNotNull('biometric_user_id')
            ->where('status', 'active')
            ->with(['shift', 'transfers.fromShift', 'transfers.toShift'])
            ->get()
            ->keyBy('biometric_user_id');

        // Pre-load device→branch map (cached, no per-punch query)
        $deviceBranchMap = \App\Models\BiometricDevice::whereNotNull('branch_id')
            ->pluck('branch_id', 'serial_number');

        $lines     = explode("\n", trim($data));
        $processed = 0;

        foreach ($lines as $line) {
            if (str_starts_with($line, 'ATTLOG')) {
                $parts = explode("\t", $line);
                if (count($parts) >= 4) {
                    $userId    = $parts[1] ?? null;
                    $timestamp = $parts[2] ?? null;
                    $status    = (int)($parts[3] ?? 0); // 0=in, 1=out

                    if ($userId && $timestamp) {
                        $punchTime = Carbon::parse($timestamp);
                        $punchType = $status === 1 ? 'out' : 'in';

                        $log = BiometricLog::create([
                            'device_serial'     => $sn,
                            'biometric_user_id' => $userId,
                            'punch_time'        => $punchTime,
                            'punch_type'        => $punchType,
                            'verify_type'       => (int)($parts[4] ?? 0),
                            'processed'         => false,
                        ]);

                        // ── BUG-005 FIX: use cached map, no extra query ───────
                        $employee = $employeeMap->get($userId);
                        if ($employee) {
                            $log->update(['employee_id' => $employee->id]);
                            $this->processAttendanceLog($employee, $log, $deviceBranchMap);
                        }
                        $processed++;
                    }
                }
            }
        }

        BiometricDevice::where('serial_number', $sn)->update(['last_online' => now()]);
        return response("OK: {$processed} records", 200)->header('Content-Type', 'text/plain');
    }

    // Device command acknowledgment
    public function deviceCommand(Request $request)
    {
        $sn = $request->SN;
        BiometricDevice::where('serial_number', $sn)->update(['last_online' => now()]);
        return response('', 200);
    }

    // ── BUG-001 & BUG-015 FIX: Use shift-based weekend detection + getShiftForDate ──
    private function processAttendanceLog(Employee $employee, BiometricLog $log, $deviceBranchMap = null): void
    {
        $date      = $log->punch_time->format('Y-m-d');
        $dayName   = strtolower(Carbon::parse($date)->format('l'));
        $branchId  = $deviceBranchMap ? $deviceBranchMap->get($log->device_serial) : null;

        // BUG-015 FIX: use getShiftForDate() — respects transfer history
        $shift     = $employee->getShiftForDate($date);

        $existing  = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $date)->first();

        if (!$existing) {
            // First punch = clock in
            $late   = 0;
            $status = 'present';

            if ($shift) {
                $shiftStart = Carbon::parse($date . ' ' . $shift->start_time);
                $late = max(0, $log->punch_time->diffInMinutes($shiftStart, false) * -1);
                if ($late > ($shift->grace_minutes ?? 0)) $status = 'late';
            }

            // BUG-001 FIX: shift-based weekend detection (Friday = weekend for BD companies)
            $isWorkingDay = $shift
                ? (bool)($shift->$dayName)
                : !Carbon::parse($date)->isWeekend();

            $holiday  = Holiday::whereDate('date', $date)->first();
            $finalSt  = match(true) {
                !is_null($holiday) => 'holiday',
                !$isWorkingDay     => 'weekend',
                default            => $status,
            };

            $att = Attendance::create([
                'employee_id'      => $employee->id,
                'date'             => $date,
                'in_time'          => $log->punch_time->format('H:i:s'),
                'in_device_serial' => $log->device_serial,
                // Fallback to employee's branch if device has no branch assigned
                'in_branch_id'     => $branchId ?? $employee->branch_id,
                'status'           => $finalSt,
                'late_minutes'     => $late,
                'source'           => 'biometric',
            ]);

            if ($holiday || !$isWorkingDay) {
                ExtraPresentRequest::firstOrCreate(
                    ['employee_id' => $employee->id, 'date' => $date],
                    [
                        'attendance_id' => $att->id,
                        'day_type'      => $holiday ? 'holiday' : 'weekend',
                        'holiday_name'  => $holiday?->name,
                        'extra_pay'     => $employee->daily_rate,
                    ]
                );
            }

        } else {
            // Subsequent punch = clock out
            $workingMinutes = $existing->in_time
                ? $log->punch_time->diffInMinutes(Carbon::parse($date . ' ' . $existing->in_time))
                : null;

            $overtime = 0;
            if ($shift && $workingMinutes) {
                $overtime = max(0, $workingMinutes - ($shift->working_minutes ?? 480));
            }

            $existing->update([
                'out_time'          => $log->punch_time->format('H:i:s'),
                'out_device_serial' => $log->device_serial,
                // Fallback to employee's branch if device has no branch assigned
                'out_branch_id'     => $branchId ?? $employee->branch_id,
                'working_minutes'   => $workingMinutes,
                'overtime_minutes'  => $overtime,
            ]);
        }

        $log->update(['processed' => true]);
    }
}
