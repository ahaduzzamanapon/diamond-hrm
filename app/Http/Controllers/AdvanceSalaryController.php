<?php

namespace App\Http\Controllers;

use App\Models\AdvanceSalary;
use App\Models\AdvanceSalaryInstallment;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdvanceSalaryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get employees for dropdown only if user is HR/Admin (can manage)
        $employees = [];
        $isHr = $user->hasPermissionTo('manage_employees'); // Or check role 'hr', 'hr-admin'
        if ($isHr) {
            $employees = Employee::where('status', 'active')->get();
        }

        // Query Advance Salaries
        $query = AdvanceSalary::with('employee');
        if (!$isHr && $user->employee) {
            $query->where('employee_id', $user->employee->id);
        }
        $advanceSalaries = $query->latest()->get();

        return view('advance_salary.index', compact('advanceSalaries', 'employees', 'isHr'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'        => 'nullable|exists:employees,id',
            'amount'             => 'required|numeric|min:1',
            'received_date'      => 'required|date',
            'start_deduct_month' => 'required',
            'installment_count'  => 'required|integer|min:1',
            'installments'       => 'required|array',
            'installments.*.month'  => 'required|string',
            'installments.*.amount' => 'required|numeric|min:1',
            'action'             => 'required|in:draft,submit'
        ]);

        $user = Auth::user();
        $isHr = $user->hasPermissionTo('manage_employees');

        // Determine correct Employee ID
        if (!$isHr) {
            if (!$user->employee) {
                return back()->with('error', 'You do not have a linked employee record.');
            }
            $employeeId = $user->employee->id;
        } else {
            $employeeId = $request->employee_id ?? ($user->employee ? $user->employee->id : null);
            if (!$employeeId) {
                return back()->with('error', 'Please select an employee.');
            }
        }

        // Verify installments sum
        $sum = collect($request->installments)->sum('amount');
        if (round($sum, 2) != round($request->amount, 2)) {
            return back()->with('error', 'Installments total must match the requested amount.');
        }

        DB::transaction(function () use ($request, $employeeId) {
            $status = $request->action === 'draft' ? 'draft' : 'pending';

            $advance = AdvanceSalary::create([
                'employee_id'        => $employeeId,
                'amount'             => $request->amount,
                'received_date'      => $request->received_date,
                'start_deduct_month' => $request->start_deduct_month,
                'installment_count'  => $request->installment_count,
                'reason'             => $request->reason,
                'status'             => $status,
            ]);

            foreach ($request->installments as $index => $inst) {
                AdvanceSalaryInstallment::create([
                    'advance_salary_id' => $advance->id,
                    'installment_no'    => $index + 1,
                    'deduct_month'      => $inst['month'],
                    'amount'            => $inst['amount'],
                    'is_deducted'       => false
                ]);
            }
        });

        return back()->with('success', 'Advanced Salary Request ' . ($request->action === 'draft' ? 'saved as draft' : 'submitted successfully') . '!');
    }

    public function show(AdvanceSalary $advanceSalary)
    {
        $user = Auth::user();
        $isHr = $user->hasPermissionTo('manage_employees');

        if (!$isHr && $user->employee && $advanceSalary->employee_id != $user->employee->id) {
            abort(403, 'Unauthorized viewing of this record.');
        }

        $advanceSalary->load('employee', 'installments');
        return view('advance_salary.show', compact('advanceSalary'));
    }
}
