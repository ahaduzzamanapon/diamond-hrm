<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\AdvanceSalary;
use App\Models\AdvanceSalaryInstallment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $month  = $request->get('month', date('Y-m'));
        $status = $request->get('status', 'active');

        // ── BUG-004 FIX: Only load active employees ────────────────────────
        $employees = Employee::with(['designation', 'department', 'payrolls' => function($q) use ($month) {
                $q->where('salary_month', $month);
            }])
            ->where('status', 'active')
            ->get();

        $branches     = \App\Models\Branch::all();
        $departments  = \App\Models\Department::all();
        $designations = \App\Models\Designation::all();

        $payrolls = Payroll::where('salary_month', $month)->get();

        return view('payroll.index', compact('employees', 'month', 'status', 'payrolls', 'branches', 'departments', 'designations'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'month'        => 'required',
            'employee_ids' => 'required|array',
            'action_type'  => 'required|in:process,final',
        ]);

        $monthStr    = $request->month;
        $employeeIds = $request->employee_ids;
        $isFinal     = ($request->action_type === 'final');

        DB::transaction(function () use ($monthStr, $employeeIds, $isFinal) {
            $carbonMonth = Carbon::parse($monthStr . '-01');
            $daysInMonth = $carbonMonth->daysInMonth;

            foreach ($employeeIds as $empId) {
                // ── BUG-004 FIX: Only process active employees ─────────────
                $employee = Employee::where('status', 'active')->find($empId);
                if (!$employee) continue;

                $payroll = Payroll::firstOrNew([
                    'employee_id'  => $empId,
                    'salary_month' => $monthStr,
                ]);

                // Don't modify if already final
                if ($payroll->status === 'final') continue;

                // ── Salary components ──────────────────────────────────────
                $basic     = $employee->basic_salary ?? 0;
                $house     = $employee->house_rent_allowance ?? 0;
                $medical   = $employee->medical_allowance ?? 0;
                $transport = $employee->transport_allowance ?? 0;
                $gross     = $basic + $house + $medical + $transport;

                // ── Attendance counts ──────────────────────────────────────
                $attendances = \App\Models\Attendance::where('employee_id', $empId)
                    ->whereMonth('date', $carbonMonth->month)
                    ->whereYear('date', $carbonMonth->year)
                    ->get();

                $presentDays = $attendances->where('status', 'present')->count();
                $lateDays    = $attendances->where('status', 'late')->count();
                $absentDays  = $attendances->where('status', 'absent')->count();
                $halfDays    = $attendances->where('status', 'half_day')->count();
                // ── BUG-008 FIX: count half_day ───────────────────────────
                $leaveDays   = $attendances->where('status', 'leave')->count();
                $holidayDays = $attendances->where('status', 'holiday')->count();
                $weekendDays = $attendances->where('status', 'weekend')->count();

                // ── BUG-003 FIX: use actual working days not daysInMonth ───
                // Working days = all days that are not weekend/holiday
                $workingDays = $attendances
                    ->whereNotIn('status', ['weekend', 'holiday'])
                    ->count();
                // If no attendance processed yet, fall back to calendar working days
                if ($workingDays === 0) $workingDays = $daysInMonth;

                // Penalty: 3 Late = 1 Absent deduction
                $latePenaltyAbsents = (int)floor($lateDays / 3);
                // Half day = 0.5 day deduction
                $halfDayDeductions  = $halfDays * 0.5;
                $totalUnpaidAbsents = $absentDays + $latePenaltyAbsents + $halfDayDeductions;

                $paidDays = max(0, $workingDays - $totalUnpaidAbsents);

                // Daily salary based on working days
                $dailySalary = $workingDays > 0 ? ($gross / $workingDays) : 0;

                $absentDed  = $absentDays * $dailySalary;
                $lateDed    = $latePenaltyAbsents * $dailySalary;
                $halfDayDed = $halfDays * ($dailySalary * 0.5);

                // Advance salary deductions
                $advanceDed   = 0;
                $installments = AdvanceSalaryInstallment::where('deduct_month', $monthStr)
                    ->where('is_deducted', false)
                    ->whereHas('advanceSalary', function($q) use ($empId) {
                        $q->where('employee_id', $empId)->where('status', 'approved');
                    })->get();

                foreach ($installments as $inst) {
                    $advanceDed += $inst->amount;
                    if ($isFinal) {
                        $inst->update(['is_deducted' => true]);
                    }
                }

                $totalDed = $absentDed + $lateDed + $halfDayDed + $advanceDed;

                // ── BUG-009 FIX: net salary minimum 0 ─────────────────────
                $netSalary = max(0, $gross - $totalDed);

                $payroll->present_days  = $presentDays;
                $payroll->absent_days   = $absentDays;
                $payroll->late_days     = $lateDays;
                $payroll->leave_days    = $leaveDays;
                $payroll->holiday_days  = $holidayDays;
                $payroll->weekend_days  = $weekendDays;
                $payroll->paid_days     = $paidDays;

                $payroll->basic_salary  = $basic;
                $payroll->house_rent    = $house;
                $payroll->medical       = $medical;
                $payroll->transport     = $transport;
                $payroll->gross_salary  = $gross;

                $payroll->absent_deduction         = $absentDed + $halfDayDed;
                $payroll->late_deduction           = $lateDed;
                $payroll->advance_salary_deduction = $advanceDed;
                $payroll->total_deduction          = $totalDed;

                $payroll->net_salary = $netSalary;
                $payroll->status     = $isFinal ? 'final' : 'draft';
                $payroll->save();
            }
        });

        $msg = $isFinal ? 'Salaries Finalized successfully!' : 'Salaries Processed safely (Draft)!';
        return response()->json(['success' => true, 'message' => $msg]);
    }

    public function reportModal(Request $request)
    {
        $month = $request->get('month', date('Y-m'));
        $type  = $request->get('type', 'salary_sheet');

        $payrolls = Payroll::with(['employee.designation', 'employee.department'])
            ->where('salary_month', $month)
            ->get();

        $viewMap  = [
            'salary_sheet' => 'salary_sheet',
            'payslip'      => 'payslip',
            'bank'         => 'bank_sheet',
            'cash'         => 'cash_sheet',
        ];
        $viewName = $viewMap[$type] ?? $type;

        return view('payroll.modals.' . $viewName, compact('payrolls', 'month'));
    }
}
