<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\BiometricDevice;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AprilAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $year  = 2026;
        $month = 4;

        // ── Load all active employees with shift & transfers ──────────────────
        $employees = Employee::where('status', 'active')
            ->with(['shift', 'transfers.fromShift', 'transfers.toShift', 'branch'])
            ->get();

        if ($employees->isEmpty()) {
            $this->command->error('No active employees found!');
            return;
        }

        // ── Load all branches and devices ─────────────────────────────────────
        $branches = Branch::where('is_active', true)->get();
        // Map: branch_id → [device_serial, ...]
        $devicesByBranch = BiometricDevice::where('is_active', true)
            ->whereNotNull('branch_id')
            ->get()
            ->groupBy('branch_id');

        // Flatten all devices for random device assignment
        $allDevices = BiometricDevice::where('is_active', true)->whereNotNull('branch_id')->get();

        // ── Load holidays for April ───────────────────────────────────────────
        $holidays = Holiday::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('name', 'date')
            ->mapWithKeys(fn($name, $d) => [Carbon::parse($d)->format('Y-m-d') => $name]);

        $start     = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $end       = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        $now       = now()->format('Y-m-d H:i:s');
        $totalRows = 0;

        // ── Delete all existing April attendance first ─────────────────────────
        $this->command->warn('🗑  Deleting existing April attendance records...');
        Attendance::whereYear('date', $year)->whereMonth('date', $month)->delete();
        $this->command->info('   Done. Seeding fresh data...');

        // ── Per-employee seeding ──────────────────────────────────────────────
        foreach ($employees as $emp) {
            $rows = [];
            $cur  = $start->copy();

            // Decide if this employee should have cross-branch punches (20% of employees)
            $hasCrossBranch = $allDevices->count() > 1 && $rand = (rand(1, 100) <= 20);

            // Pick a different branch for OUT punch (for cross-branch employees)
            $crossBranchDevice = null;
            if ($hasCrossBranch && $allDevices->count() > 1) {
                // Filter out employee's own branch devices
                $otherDevices = $allDevices->where('branch_id', '!=', $emp->branch_id);
                if ($otherDevices->isNotEmpty()) {
                    $crossBranchDevice = $otherDevices->random();
                } else {
                    $hasCrossBranch = false;
                }
            }

            // Pick the employee's own branch device for IN punch
            $ownBranchDevices = $devicesByBranch->get($emp->branch_id, collect());
            $ownDevice        = $ownBranchDevices->isNotEmpty() ? $ownBranchDevices->random() : null;

            while ($cur->lte($end)) {
                $dateStr = $cur->format('Y-m-d');
                $dayName = strtolower($cur->format('l'));

                // Get shift for this date (respects transfer history)
                $shift = $emp->getShiftForDate($dateStr);

                // Determine if working day using shift config
                $isWorkingDay = $shift
                    ? (bool)($shift->$dayName)
                    : !$cur->isWeekend();

                $holiday = $holidays->get($dateStr);

                // ── Decide status ─────────────────────────────────────────────
                if ($holiday) {
                    $rows[] = $this->makeRow($emp->id, $dateStr, 'holiday', null, null, null, null, 0, 0, $holiday, $now);
                    $cur->addDay();
                    continue;
                }

                if (!$isWorkingDay) {
                    $rows[] = $this->makeRow($emp->id, $dateStr, 'weekend', null, null, null, null, 0, 0, null, $now);
                    $cur->addDay();
                    continue;
                }

                // Working day — random outcome
                $rand = rand(1, 100);

                if ($rand <= 88) {
                    // 88% present / late
                    [$inTime, $outTime, $late, $working, $overtime] = $this->generateTimes($shift, $dateStr);
                    $status = $late > ($shift?->grace_minutes ?? 0) ? 'late' : 'present';

                    // Cross-branch: employee punches IN at own branch, OUT at different branch
                    // On random days (not every day) for cross-branch employees
                    $usesCross = $hasCrossBranch && rand(1, 100) <= 40; // 40% of their working days
                    $inDevice  = $ownDevice;
                    $outDevice = $usesCross ? $crossBranchDevice : $ownDevice;

                    // Fallback: if no device, use employee's branch_id directly
                    $inBranchId  = $inDevice?->branch_id  ?? $emp->branch_id;
                    $outBranchId = $outDevice?->branch_id ?? ($usesCross ? null : $emp->branch_id);

                    $rows[] = $this->makeRow(
                        $emp->id, $dateStr, $status, $inTime, $outTime,
                        $inDevice?->serial_number, $outDevice?->serial_number,
                        $late, $working, null, $now, $overtime,
                        $inBranchId, $outBranchId
                    );
                } elseif ($rand <= 95) {
                    // 7% absent
                    $rows[] = $this->makeRow($emp->id, $dateStr, 'absent', null, null, null, null, 0, 0, null, $now);
                } else {
                    // 5% half-day
                    [$inTime, , $late, , ] = $this->generateTimes($shift, $dateStr);
                    $halfOut = $shift
                        ? Carbon::parse($dateStr . ' ' . $shift->start_time)->addHours(4)->format('H:i:s')
                        : Carbon::parse($dateStr . ' 13:00:00')->format('H:i:s');
                    $workMin = 4 * 60;

                    $usesCross = $hasCrossBranch && rand(1, 100) <= 40;
                    $inDevice  = $ownDevice;
                    $outDevice = $usesCross ? $crossBranchDevice : $ownDevice;

                    // Fallback: if no device, use employee's branch_id directly
                    $inBranchId  = $inDevice?->branch_id  ?? $emp->branch_id;
                    $outBranchId = $outDevice?->branch_id ?? ($usesCross ? null : $emp->branch_id);

                    $rows[] = $this->makeRow(
                        $emp->id, $dateStr, 'half_day', $inTime, $halfOut,
                        $inDevice?->serial_number, $outDevice?->serial_number,
                        $late, $workMin, null, $now, 0,
                        $inBranchId, $outBranchId
                    );
                }

                $cur->addDay();
            }

            // ── Bulk insert ────────────────────────────────────────────────────
            foreach (array_chunk($rows, 100) as $chunk) {
                Attendance::insert($chunk);
            }

            $totalRows += count($rows);
            $crossNote  = $hasCrossBranch ? ' [cross-branch ⚡]' : '';
            $this->command->line("  ✔ {$emp->name} → " . count($rows) . " days{$crossNote}  (shift: " . ($emp->shift?->name ?? 'none') . ')');
        }

        $this->command->info("\n✅ Done! {$totalRows} attendance records seeded for " . $employees->count() . " employees (April {$year}).");
        $this->command->warn("   ℹ️  Cross-branch punches are highlighted in Register Sheet as ⚠️ red OUT Branch.");
    }

    // ── Generate realistic in / out times ─────────────────────────────────────
    private function generateTimes(?object $shift, string $date): array
    {
        $startBase = $shift ? $shift->start_time : '09:00:00';
        $endBase   = $shift ? $shift->end_time   : '18:00:00';

        $offsetIn  = rand(-5, 30);
        $inTime    = Carbon::parse($date . ' ' . $startBase)->addMinutes($offsetIn)->format('H:i:s');

        $shiftStart = Carbon::parse($date . ' ' . $startBase);
        $actualIn   = Carbon::parse($date . ' ' . $inTime);
        $late       = max(0, $actualIn->diffInMinutes($shiftStart, false) * -1);

        $offsetOut = rand(-15, 60);
        $outTime   = Carbon::parse($date . ' ' . $endBase)->addMinutes($offsetOut)->format('H:i:s');

        $workingMinutes  = max(0, Carbon::parse($date . ' ' . $outTime)->diffInMinutes($actualIn));
        $shiftMinutes    = $shift?->working_minutes ?? 480;
        $overtimeMinutes = max(0, $workingMinutes - $shiftMinutes);

        return [$inTime, $outTime, (int)$late, $workingMinutes, $overtimeMinutes];
    }

    // ── Build a row array ─────────────────────────────────────────────────────
    private function makeRow(
        int     $empId,
        string  $date,
        string  $status,
        ?string $inTime,
        ?string $outTime,
        ?string $inDeviceSerial,
        ?string $outDeviceSerial,
        int     $lateMin,
        int     $workMin,
        ?string $note,
        string  $now,
        int     $overtime  = 0,
        ?int    $inBranchId  = null,
        ?int    $outBranchId = null,
    ): array {
        return [
            'employee_id'       => $empId,
            'date'              => $date,
            'status'            => $status,
            'in_time'           => $inTime,
            'in_device_serial'  => $inDeviceSerial,
            'in_branch_id'      => $inBranchId,
            'out_time'          => $outTime,
            'out_device_serial' => $outDeviceSerial,
            'out_branch_id'     => $outBranchId,
            'late_minutes'      => $lateMin,
            'working_minutes'   => $workMin,
            'overtime_minutes'  => $overtime,
            'source'            => 'manual',
            'note'              => $note,
            'entered_by'        => null,
            'created_at'        => $now,
            'updated_at'        => $now,
        ];
    }
}
