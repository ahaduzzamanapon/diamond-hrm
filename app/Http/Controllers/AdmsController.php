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
 *   (No /adms prefix — device sends to /adms/* automatically)
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
        // Return empty response — device expects 200 OK
        return response('', 200)->header('Content-Type', 'text/plain');
    }

    // Attendance push from device
    public function attendance(Request $request)
    {
        Log::info('ADMS Attendance Push', $request->all());

        $sn   = $request->SN ?? 'UNKNOWN';
        $data = $request->getContent();

        // ZKTeco ADMS format: ATTLOG\tUSERID\tTIMESTAMP\tSTATUS\tVERIFY\n
        $lines = explode("\n", trim($data));
        $processed = 0;

        foreach ($lines as $line) {
            if (str_starts_with($line, 'ATTLOG')) {
                $parts = explode("\t", $line);
                // ATTLOG  UserID  Timestamp  Status  Verify
                if (count($parts) >= 4) {
                    $userId    = $parts[1] ?? null;
                    $timestamp = $parts[2] ?? null;
                    $status    = (int)($parts[3] ?? 0); // 0=in, 1=out

                    if ($userId && $timestamp) {
                        $punchTime  = Carbon::parse($timestamp);
                        $punchType  = $status === 1 ? 'out' : 'in';

                        $log = BiometricLog::create([
                            'device_serial'     => $sn,
                            'biometric_user_id' => $userId,
                            'punch_time'        => $punchTime,
                            'punch_type'        => $punchType,
                            'verify_type'       => (int)($parts[4] ?? 0),
                            'processed'         => false,
                        ]);

                        // Map to employee and create/update attendance
                        $employee = Employee::where('biometric_user_id', $userId)->first();
                        if ($employee) {
                            $log->update(['employee_id' => $employee->id]);
                            $this->processAttendanceLog($employee, $log);
                        }
                        $processed++;
                    }
                }
            }
        }

        // Update device heartbeat
        BiometricDevice::where('serial_number',$sn)->update(['last_online'=>now()]);

        return response("OK: {$processed} records", 200)->header('Content-Type','text/plain');
    }

    // Device command acknowledgment
    public function deviceCommand(Request $request)
    {
        $sn = $request->SN;
        BiometricDevice::where('serial_number',$sn)->update(['last_online'=>now()]);
        return response('', 200);
    }

    private function processAttendanceLog(Employee $employee, BiometricLog $log): void
    {
        $date     = $log->punch_time->format('Y-m-d');
        $shift    = $employee->shift;
        $existing = Attendance::where('employee_id',$employee->id)->whereDate('date',$date)->first();

        if (!$existing) {
            // First punch = clock in
            $late = 0;
            $status = 'present';
            if ($shift) {
                $shiftStart = Carbon::parse($date . ' ' . $shift->start_time);
                $late = max(0, $log->punch_time->diffInMinutes($shiftStart, false) * -1);
                if ($late > $shift->grace_minutes) $status = 'late';
            }

            // Check holiday/weekend
            $holiday  = Holiday::whereDate('date',$date)->first();
            $dayOfWeek= $log->punch_time->dayOfWeek;
            $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);

            $att = Attendance::create([
                'employee_id'  => $employee->id,
                'date'         => $date,
                'in_time'      => $log->punch_time->format('H:i:s'),
                'status'       => ($holiday || $isWeekend) ? ($holiday ? 'holiday' : 'weekend') : $status,
                'late_minutes' => $late,
                'source'       => 'biometric',
            ]);

            // Auto-create extra present request for holiday/weekend
            if ($holiday || $isWeekend) {
                ExtraPresentRequest::firstOrCreate(
                    ['employee_id'=>$employee->id,'date'=>$date],
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
                ? $log->punch_time->diffInMinutes(Carbon::parse($date.' '.$existing->in_time))
                : null;

            $overtime = 0;
            if ($shift && $workingMinutes) {
                $expected = $shift->working_minutes;
                $overtime = max(0, $workingMinutes - $expected);
            }

            $existing->update([
                'out_time'         => $log->punch_time->format('H:i:s'),
                'working_minutes'  => $workingMinutes,
                'overtime_minutes' => $overtime,
            ]);
        }

        $log->update(['processed'=>true]);
    }
}
