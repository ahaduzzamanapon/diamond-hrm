<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BiometricDevice;
use App\Models\BiometricLog;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BiometricController extends Controller
{
    public function devices()
    {
        $user      = Auth::user();
        $devices   = BiometricDevice::with('branch')
            ->when(!$user->hasPermissionTo('view_all_branches'), fn($q) => $q->where('branch_id',$user->branch_id))
            ->get();
        $branches  = $user->hasPermissionTo('view_all_branches') ? Branch::all() : Branch::where('id',$user->branch_id)->get();
        $totalLogs = BiometricLog::count();
        $unprocessed = BiometricLog::where('processed', false)->count();
        return view('biometric.devices', compact('devices','branches','totalLogs','unprocessed'));
    }

    public function storeDevice(Request $request)
    {
        $request->validate([
            'name'          => 'required',
            'serial_number' => 'required|unique:biometric_devices,serial_number',
            'branch_id'     => 'required|exists:branches,id',
        ]);
        BiometricDevice::create($request->only('name','serial_number','ip_address','model','branch_id','is_active'));
        return back()->with('success','Device added!');
    }

    public function destroyDevice(BiometricDevice $device)
    {
        $device->delete();
        return back()->with('success','Device removed.');
    }

    public function logs(Request $request)
    {
        $user  = Auth::user();
        $month = $request->month ?? now()->format('Y-m');
        [$yr,$mn] = explode('-',$month);

        $logs = BiometricLog::with(['employee.branch','employee.designation', 'device'])
            ->when($request->employee_id, fn($q) => $q->where('employee_id',$request->employee_id))
            ->when(!$user->hasPermissionTo('view_all_branches'), function($q) use ($user) {
                // Allows mapped branch logs OR unmapped device branch logs
                $q->where(function($query) use ($user) {
                    $query->whereHas('employee', fn($e) => $e->where('branch_id', $user->branch_id))
                          ->orWhere(function($sub) use ($user) {
                              $sub->whereNull('employee_id')
                                  ->whereHas('device', fn($d) => $d->where('branch_id', $user->branch_id));
                          });
                });
            });

        \Illuminate\Support\Facades\Log::info("User ID: {$user->id}, Is SuperAdmin: " . ($user->hasPermissionTo('view_all_branches') ? 'Yes' : 'No'));
        \Illuminate\Support\Facades\Log::info($logs->toSql(), $logs->getBindings());

        $logs = $logs->whereYear('punch_time',$yr)->whereMonth('punch_time',$mn)
            ->orderByDesc('punch_time')->paginate(50)->withQueryString();

        $employees = Employee::forUser($user)->where('status','active')->get();
        return view('biometric.logs', compact('logs','employees','month'));
    }

    public function mapping(Request $request)
    {
        $user      = Auth::user();
        $employees = Employee::with('branch','designation')
            ->forUser($user)->where('status','active')
            ->get();
        return view('biometric.mapping', compact('employees'));
    }

    public function updateMapping(Request $request)
    {
        foreach ($request->mappings ?? [] as $empId => $bioId) {
            \App\Models\Employee::where('id',$empId)->update(['biometric_user_id' => $bioId ?: null]);
            if ($bioId) {
                // Retroactively map unprocessed biometric logs with this UID to the employee
                \App\Models\BiometricLog::where('biometric_user_id', $bioId)
                    ->whereNull('employee_id')
                    ->update(['employee_id' => $empId]);
            }
        }
        return back()->with('success','Biometric mapping updated! Existing unprocessed logs are now mapped to the respective employees.');
    }

    public function sync(Request $request)
    {
        $deviceId = $request->device;
        $limit    = (int)($request->limit ?? 500);

        // Load transfers with shifts so getShiftForDate() has no N+1
        $logs = BiometricLog::with([
                'employee.shift',
                'employee.transfers.fromShift',
                'employee.transfers.toShift',
            ])
            ->where('processed', false)
            ->whereNotNull('employee_id')
            ->when($deviceId, function($q) use ($deviceId) {
                $device = \App\Models\BiometricDevice::find($deviceId);
                if ($device) $q->where('device_serial', $device->serial_number);
            })
            ->orderBy('punch_time')
            ->limit($limit)
            ->get();

        if ($logs->isEmpty()) {
            return back()->with('info', 'No unprocessed logs found.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($logs as $log) {
            $employee = $log->employee;
            if (!$employee) { $skipped++; continue; }

            $date         = Carbon::parse($log->punch_time)->format('Y-m-d');
            $punchTime    = Carbon::parse($log->punch_time)->format('H:i:s');
            $dayName      = strtolower(Carbon::parse($date)->format('l')); // 'monday','friday' etc.

            // Get shift for this date — respects transfer history, no N+1
            $shiftForDate = $employee->getShiftForDate($date);

            $existing = Attendance::where('employee_id', $employee->id)
                ->where('date', $date)->first();

            if (!$existing) {
                // ── First punch of the day: clock-in ──────────────────────────
                $late   = 0;
                $status = 'present';

                if ($shiftForDate) {
                    $shiftStart = Carbon::parse($date . ' ' . $shiftForDate->start_time);
                    $late = max(0, Carbon::parse($log->punch_time)->diffInMinutes($shiftStart, false) * -1);
                    if ($late > ($shiftForDate->grace_minutes ?? 0)) $status = 'late';
                }

                // Shift-based weekend check (respects friday=weekend for BD companies)
                $isWorkingDay = $shiftForDate
                    ? (bool)($shiftForDate->$dayName)
                    : !Carbon::parse($date)->isWeekend();

                $holiday = Holiday::where('date', $date)->first();
                $finalSt = match(true) {
                    !is_null($holiday) => 'holiday',
                    !$isWorkingDay     => 'weekend',
                    default            => $status,
                };

                $att = Attendance::create([
                    'employee_id'  => $employee->id,
                    'date'         => $date,
                    'in_time'      => $punchTime,
                    'status'       => $finalSt,
                    'late_minutes' => $late,
                    'source'       => 'biometric',
                ]);

                // Auto-create extra present request for holiday/weekend punches
                if ($holiday || !$isWorkingDay) {
                    \App\Models\ExtraPresentRequest::firstOrCreate(
                        ['employee_id' => $employee->id, 'date' => $date],
                        [
                            'attendance_id' => $att->id,
                            'day_type'      => $holiday ? 'holiday' : 'weekend',
                            'holiday_name'  => $holiday?->name,
                            'extra_pay'     => $employee->daily_rate ?? 0,
                        ]
                    );
                }
                $created++;

            } else {
                // ── Existing record found ──────────────────────────────────────
                if (!$existing->in_time) {
                    // ⚠️ Record exists but NO in_time yet
                    // This happens when processAttendance ran before sync
                    // Treat this punch as clock-in
                    $late   = 0;
                    $status = 'present';

                    if ($shiftForDate) {
                        $shiftStart = Carbon::parse($date . ' ' . $shiftForDate->start_time);
                        $late = max(0, Carbon::parse($log->punch_time)->diffInMinutes($shiftStart, false) * -1);
                        if ($late > ($shiftForDate->grace_minutes ?? 0)) $status = 'late';
                    }

                    $existing->update([
                        'in_time'      => $punchTime,
                        'status'       => $status,
                        'late_minutes' => $late,
                        'source'       => 'biometric',
                    ]);
                    $updated++;
                } else {
                    // Has in_time → this is clock-out
                    $workMin  = Carbon::parse($log->punch_time)->diffInMinutes(
                        Carbon::parse($date . ' ' . $existing->in_time)
                    );
                    $overtime = ($shiftForDate && $workMin)
                        ? max(0, $workMin - ($shiftForDate->working_minutes ?? 480))
                        : 0;

                    $existing->update([
                        'out_time'         => $punchTime,
                        'working_minutes'  => $workMin,
                        'overtime_minutes' => $overtime,
                    ]);
                    $updated++;
                }
            }

            $log->update(['processed' => true]);
        }

        $msg = "Sync complete — Created: {$created}, Clock-out updated: {$updated}, Skipped: {$skipped}.";
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }



    public function pushEmployees(Request $request, BiometricDevice $device)
    {
        // In real implementation, this queues a CREATEUSER command for each employee
        $employees = Employee::where('branch_id',$device->branch_id)->whereNotNull('biometric_user_id')->get();
        // TODO: Queue push commands
        return back()->with('success',"Push command queued for {$employees->count()} employees.");
    }
}
