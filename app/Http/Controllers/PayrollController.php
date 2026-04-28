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
        $month = $request->get('month', date('Y-m')); // default current month
        $status = $request->get('status', 'active'); // For employee filter
        
        $employees = Employee::with(['designation', 'department', 'payrolls' => function($q) use ($month) {
                $q->where('salary_month', $month);
            }])
            ->get();
            
        // Fetch variables for filtering
        $branches = \App\Models\Branch::all();
        $departments = \App\Models\Department::all();
        $designations = \App\Models\Designation::all();
            
        // Check processing status for the month
        $payrolls = Payroll::where('salary_month', $month)->get();
        
        return view('payroll.index', compact('employees', 'month', 'status', 'payrolls', 'branches', 'departments', 'designations'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'month' => 'required',
            'employee_ids' => 'required|array',
            'action_type' => 'required|in:process,final',
        ]);

        $monthStr = $request->month; // YYYY-MM
        $employeeIds = $request->employee_ids;
        $isFinal = ($request->action_type === 'final');

        DB::transaction(function () use ($monthStr, $employeeIds, $isFinal) {
            foreach ($employeeIds as $empId) {
                $employee = Employee::find($empId);
                if (!$employee) continue;

                // 1. Check existing payroll
                $payroll = Payroll::firstOrNew([
                    'employee_id' => $empId,
                    'salary_month' => $monthStr,
                ]);
                
                // Don't modify if already final
                if ($payroll->status === 'final') continue;

                // 2. Base salary & allowances
                $basic = $employee->basic_salary ?? 0;
                $house = $employee->house_rent_allowance ?? 0;
                $medical = $employee->medical_allowance ?? 0;
                $transport = $employee->transport_allowance ?? 0;
                
                $gross = $basic + $house + $medical + $transport;

                // 3. Deductions logic
                $carbonMonth = \Carbon\Carbon::parse($monthStr . '-01');
                $daysInMonth = $carbonMonth->daysInMonth;
                
                $attendances = \App\Models\Attendance::where('employee_id', $empId)
                    ->whereMonth('date', $carbonMonth->month)
                    ->whereYear('date', $carbonMonth->year)
                    ->get();
                    
                $presentDays = $attendances->where('status', 'present')->count();
                $absentDays  = $attendances->where('status', 'absent')->count();
                $lateDays    = $attendances->where('status', 'late')->count();
                $leaveDays   = $attendances->where('status', 'leave')->count();
                $holidayDays = $attendances->where('status', 'holiday')->count();
                $weekendDays = $attendances->where('status', 'weekend')->count();
                
                // Penalty: 3 Late = 1 Absent Salary Deduction
                $latePenaltyAbsents = floor($lateDays / 3);
                $totalUnpaidAbsents = $absentDays + $latePenaltyAbsents;
                
                $paidDays = $daysInMonth - $totalUnpaidAbsents;
                if ($paidDays < 0) $paidDays = 0;
                
                // Daily Salary = Gross / Days in Month
                $dailySalary = $daysInMonth > 0 ? ($gross / $daysInMonth) : 0;
                
                $absentDed = $absentDays * $dailySalary;
                $lateDed   = $latePenaltyAbsents * $dailySalary;
                
                // Advanced Salary Deduction for this month
                $advanceDed = 0;
                $installments = \App\Models\AdvanceSalaryInstallment::where('deduct_month', $monthStr)
                                    ->where('is_deducted', false)
                                    ->whereHas('advanceSalary', function($q) use ($empId) {
                                        $q->where('employee_id', $empId)->where('status', 'approved');
                                    })->get();
                                    
                foreach ($installments as $inst) {
                    $advanceDed += $inst->amount;
                    if ($isFinal) {
                        /** @var \App\Models\AdvanceSalaryInstallment $inst */
                        $inst->update(['is_deducted' => true]);
                    }
                }
                
                $totalDed = $absentDed + $lateDed + $advanceDed;

                $payroll->present_days = $presentDays;
                $payroll->absent_days  = $absentDays;
                $payroll->late_days    = $lateDays;
                $payroll->leave_days   = $leaveDays;
                $payroll->holiday_days = $holidayDays;
                $payroll->weekend_days = $weekendDays;
                $payroll->paid_days    = $paidDays;

                $payroll->basic_salary = $basic;
                $payroll->house_rent = $house;
                $payroll->medical = $medical;
                $payroll->transport = $transport;
                $payroll->gross_salary = $gross;
                
                $payroll->absent_deduction = $absentDed;
                $payroll->late_deduction = $lateDed;
                $payroll->advance_salary_deduction = $advanceDed;
                $payroll->total_deduction = $totalDed;
                
                $payroll->net_salary = $gross - $totalDed;
                $payroll->status = $isFinal ? 'final' : 'draft';
                $payroll->save();
            }
        });

        $msg = $isFinal ? 'Salaries Finalized successfully!' : 'Salaries Processed safely (Draft)!';
        return response()->json([
            'success' => true,
            'message' => $msg
        ]);
    }

    public function reportModal(Request $request)
    {
        $month = $request->get('month', date('Y-m'));
        $type = $request->get('type', 'salary_sheet'); // salary_sheet, payslip, bank, cash

        $payrolls = Payroll::with(['employee.designation', 'employee.department'])
                    ->where('salary_month', $month)
                    ->get();
                    
        $viewMap = [
            'salary_sheet' => 'salary_sheet',
            'payslip'      => 'payslip',
            'bank'         => 'bank_sheet',
            'cash'         => 'cash_sheet',
        ];
        $viewName = $viewMap[$type] ?? $type;

        return view('payroll.modals.' . $viewName, compact('payrolls', 'month'));

    }
}
