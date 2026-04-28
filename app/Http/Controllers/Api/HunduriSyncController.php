<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BiometricDevice;
use App\Models\BiometricLog;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HunduriSyncController extends Controller
{
    public function sync(Request $request)
    {
        // 1. Verify custom Token
        $expectedToken = env('HUNDURI_SYNC_TOKEN', 'hunduri-sync-secret-2026');
        $token = $request->bearerToken();
        
        if ($token !== $expectedToken) {
            return response()->json(['success' => false, 'message' => 'Unauthorized token'], 401);
        }

        $events = $request->input('events', []);
        if (empty($events)) {
            return response()->json(['success' => true, 'message' => 'No events provided']);
        }

        $savedLogs = 0;
        
        foreach ($events as $ev) {
            // "personID" is mapping to employee mapping or default tracking.
            if (empty($ev['personID'])) continue; 
            
            // Auto register the device if it doesn't exist
            $deviceName = !empty($ev['deviceName']) ? $ev['deviceName'] : 'Hunduri-' . $ev['deviceID'];
            $ipAddress  = 'HUNDURI_' . $ev['deviceID'];

            $device = BiometricDevice::firstOrCreate(
                ['serial_number' => 'HUNDURI_' . $ev['deviceID']],
                [
                    'name'          => $deviceName,
                    'ip_address'    => $ipAddress,
                    'last_online'   => now(),
                    'is_active'     => true
                ]
            );

            // Punch time extraction
            if (empty($ev['eventDate']) || empty($ev['eventTime'])) continue;
            
            // Dates from DB come like 2026/01/27 and 18:45:00
            try {
                $punchDateTime = Carbon::parse($ev['eventDate'] . ' ' . $ev['eventTime']);
            } catch (\Exception $e) {
                continue;
            }
            
            // Check if log already exists
            $exists = BiometricLog::where('device_serial', $device->serial_number)
                ->where('biometric_user_id', $ev['personID'])
                ->where('punch_time', $punchDateTime)
                ->exists();
                
            if (!$exists) {
                $employee = Employee::where('biometric_user_id', $ev['personID'])
                                    ->orWhere('employee_id', $ev['personID'])
                                    ->first();
                // Smartly determine if this is an Check-IN or Check-OUT punch
                $punchType = 'unknown';
                if ($employee) {
                    $hasPunchedToday = BiometricLog::where('biometric_user_id', $ev['personID'])
                        ->whereDate('punch_time', $punchDateTime->format('Y-m-d'))
                        ->exists();
                    $punchType = $hasPunchedToday ? 'out' : 'in';
                } else {
                    $punchType = ((int)$punchDateTime->format('H') < 14) ? 'in' : 'out';
                }

                $log = BiometricLog::create([
                    'device_serial'     => $device->serial_number,
                    'biometric_user_id' => $ev['personID'],
                    'employee_id'       => $employee ? $employee->id : null,
                    'punch_time'        => $punchDateTime,
                    'punch_type'        => $punchType,
                    'verify_type'       => !empty($ev['eventCode']) ? (int)$ev['eventCode'] : 0,
                    'processed'         => false
                ]);
                $savedLogs++;
                
                // Immediately Process it if employee bound
                if ($employee) {
                   $this->processAttendanceForLog($log, $employee, $punchDateTime);
                   $log->update(['processed' => true]);
                }
            }
        }
        
        return response()->json(['success' => true, 'message' => "Successfully synced {$savedLogs} biometric records."]);
    }

    private function processAttendanceForLog($log, $employee, Carbon $punchDateTime)
    {
        $date = $punchDateTime->format('Y-m-d');
        $shift = $employee->shift;
        $existing = Attendance::where('employee_id', $employee->id)->where('date', $date)->first();

        if (!$existing) {
            $late = 0;
            $status = 'present';
            if ($shift) {
                $shiftStart = Carbon::parse($date . ' ' . $shift->start_time);
                $late = max(0, $punchDateTime->diffInMinutes($shiftStart, false) * -1);
                if ($late > ($shift->grace_minutes ?? 0)) $status = 'late';
            }

            $holiday = Holiday::where('date', $date)->first();
            $isWeekend = Carbon::parse($date)->isWeekend();
            $finalSt = ($holiday || $isWeekend) ? ($holiday ? 'holiday' : 'weekend') : $status;

            Attendance::create([
                'employee_id'  => $employee->id,
                'date'         => $date,
                'in_time'      => $punchDateTime->format('H:i:s'),
                'status'       => $finalSt,
                'late_minutes' => $late,
                'source'       => 'biometric',
            ]);
        } else {
            // Append out time
            if (!$existing->in_time) {
                $existing->update(['in_time' => $punchDateTime->format('H:i:s')]);
            } else {
                $existing->update(['out_time' => $punchDateTime->format('H:i:s')]);
            }
        }
    }
}
