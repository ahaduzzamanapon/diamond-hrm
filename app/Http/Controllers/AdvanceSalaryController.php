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

        // BUG-109 FIX: branch-based access control
        $isHr = $user->hasPermissionTo('manage_employees');
        $employees = $isHr
            ? Employee::where('status', 'active')->get()
            : [];

        $query = AdvanceSalary::with(['employee.branch', 'installments']);
        if ($isHr && $user->hasPermissionTo('view_all_branches')) {
            // super-admin sees all
        } elseif ($isHr) {
            // branch HR — only their branch
            $query->whereHas('employee', fn($e) => $e->where('branch_id', $user->branch_id));
        } else {
            // regular staff — own records only
            if ($user->employee) {
                $query->where('employee_id', $user->employee->id);
            } else {
                $query->whereRaw('0=1'); // no employee linked → empty
            }
        }

        $advanceSalaries = $query->latest()->get();

        return view('advance_salary.index', compact('advanceSalaries', 'employees', 'isHr'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'       => 'nullable|exists:employees,id',
            'amount'            => 'required|numeric|min:1',
            'received_date'     => 'required|date',
            'start_deduct_month'=> 'required',
            'installment_count' => 'required|integer|min:1',
            'installments'      => 'required|array',
            'installments.*.month'  => 'required|string',
            'installments.*.amount' => 'required|numeric|min:1',
            'action'            => 'required|in:draft,submit',
        ]);

        $user = Auth::user();
        $isHr = $user->hasPermissionTo('manage_employees');

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

        // Verify installments sum matches total amount
        $sum = collect($request->installments)->sum('amount');
        if (round($sum, 2) != round($request->amount, 2)) {
            return back()->with('error', 'Installments total must match the requested amount.');
        }

        DB::transaction(function () use ($request, $employeeId) {
            $status  = $request->action === 'draft' ? 'draft' : 'pending';
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
                    'is_deducted'       => false,
                ]);
            }
        });

        $msg = $request->action === 'draft' ? 'saved as draft' : 'submitted successfully';
        return back()->with('success', "Advanced Salary Request {$msg}!");
    }

    public function show(AdvanceSalary $advanceSalary)
    {
        $user = Auth::user();
        $isHr = $user->hasPermissionTo('manage_employees');

        // Staff can only view their own; HR can view branch's records
        if (!$isHr && $user->employee && $advanceSalary->employee_id != $user->employee->id) {
            abort(403, 'Unauthorized.');
        }

        $advanceSalary->load('employee', 'installments');
        return view('advance_salary.show', compact('advanceSalary'));
    }

    // ── BUG-104 FIX: Approve advance salary request ────────────────────────────
    public function approve(Request $request, AdvanceSalary $advanceSalary)
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('manage_employees')) {
            abort(403, 'Unauthorized.');
        }

        if (!in_array($advanceSalary->status, ['pending', 'draft'])) {
            return back()->with('error', 'Only pending/draft requests can be approved.');
        }

        $advanceSalary->update([
            'status'      => 'approved',
            'approved_by' => $user->id,
        ]);

        return back()->with('success', 'Advance salary approved!');
    }

    // ── BUG-104 FIX: Reject advance salary request ────────────────────────────
    public function reject(Request $request, AdvanceSalary $advanceSalary)
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('manage_employees')) {
            abort(403, 'Unauthorized.');
        }

        $advanceSalary->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Advance salary rejected.');
    }
}
