<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeTransfer;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeTransferController extends Controller
{
    /**
     * Show transfer history for an employee.
     */
    public function index(Employee $employee)
    {
        $transfers = $employee->transfers()
            ->with(['fromBranch','toBranch','fromDept','toDept','fromShift','toShift','transferredBy'])
            ->orderByDesc('effective_date')
            ->get();

        $branches     = Branch::where('is_active', true)->get();
        $departments  = Department::all();
        $shifts       = Shift::where('is_active', true)->get();

        return view('employees.transfers', compact('employee', 'transfers', 'branches', 'departments', 'shifts'));
    }

    /**
     * Process a new branch transfer.
     */
    public function store(Request $request, Employee $employee)
    {
        $request->validate([
            'to_branch_id'     => 'required|exists:branches,id',
            'to_department_id' => 'nullable|exists:departments,id',
            'to_shift_id'      => 'nullable|exists:shifts,id',
            'effective_date'   => 'required|date',
            'reason'           => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $employee) {
            // Save transfer record (capture current state as "from")
            EmployeeTransfer::create([
                'employee_id'        => $employee->id,
                'from_branch_id'     => $employee->branch_id,
                'from_department_id' => $employee->department_id,
                'from_shift_id'      => $employee->shift_id,
                'to_branch_id'       => $request->to_branch_id,
                'to_department_id'   => $request->to_department_id ?? $employee->department_id,
                'to_shift_id'        => $request->to_shift_id     ?? $employee->shift_id,
                'effective_date'     => $request->effective_date,
                'reason'             => $request->reason,
                'transferred_by'     => Auth::id(),
            ]);

            // Update employee's current branch/department/shift to new values
            $employee->update([
                'branch_id'     => $request->to_branch_id,
                'department_id' => $request->to_department_id ?? $employee->department_id,
                'shift_id'      => $request->to_shift_id     ?? $employee->shift_id,
            ]);

            // Also update the linked user's branch_id
            if ($employee->user) {
                $employee->user->update(['branch_id' => $request->to_branch_id]);
            }
        });

        return redirect()
            ->route('employees.transfers.index', $employee)
            ->with('success', "✅ {$employee->name} transferred successfully!");
    }
}
