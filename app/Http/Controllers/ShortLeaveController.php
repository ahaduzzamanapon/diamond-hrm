<?php

namespace App\Http\Controllers;

use App\Models\ShortLeave;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShortLeaveController extends Controller
{
    // ── List all short leave records ────────────────────────────────────────
    public function index(Request $request)
    {
        $query = ShortLeave::with('employee.branch', 'employee.department', 'enteredBy', 'approvedBy')
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        // Branch filter (role-based)
        if (!Auth::user()->hasPermissionTo('view_all_branches')) {
            $query->whereHas('employee', fn($q) => $q->where('branch_id', Auth::user()->branch_id));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('branch_id')) {
            $query->whereHas('employee', fn($q) => $q->where('branch_id', $request->branch_id));
        }

        $shortLeaves = $query->paginate(30)->withQueryString();
        $employees   = Employee::orderBy('name')->get();
        $branches    = Branch::orderBy('name')->get();

        return view('attendance.short_leave.index', compact('shortLeaves', 'employees', 'branches'));
    }

    // ── Create form ─────────────────────────────────────────────────────────
    public function create()
    {
        $employees = Employee::with('branch')
            ->forUser(Auth::user())
            ->orderBy('name')
            ->get();

        return view('attendance.short_leave.create', compact('employees'));
    }

    // ── Store ───────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'out_time'    => 'required',
            'in_time'     => 'nullable',
            'reason'      => 'required|string|max:255',
            'note'        => 'nullable|string',
        ]);

        $data['duration_minutes'] = ShortLeave::calcDuration($data['out_time'], $data['in_time'] ?? null);
        $data['entered_by']       = Auth::id();
        $data['status']           = 'pending';

        ShortLeave::create($data);

        return redirect()->route('attendance.short-leave.index')
            ->with('success', 'Short Leave entry saved successfully.');
    }

    // ── Approve ─────────────────────────────────────────────────────────────
    public function approve(ShortLeave $shortLeave)
    {
        $shortLeave->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Short Leave approved.');
    }

    // ── Reject ──────────────────────────────────────────────────────────────
    public function reject(ShortLeave $shortLeave)
    {
        $shortLeave->update([
            'status'      => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Short Leave rejected.');
    }

    // ── Report page ─────────────────────────────────────────────────────────
    public function report(Request $request)
    {
        $employees   = Employee::forUser(Auth::user())->orderBy('name')->get();
        $branches    = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        $rows = collect();
        if ($request->filled('date_from') || $request->filled('employee_id')) {
            $query = ShortLeave::with('employee.branch', 'employee.department', 'approvedBy')
                ->orderBy('date')
                ->orderBy('out_time');

            if (!Auth::user()->hasPermissionTo('view_all_branches')) {
                $query->whereHas('employee', fn($q) => $q->where('branch_id', Auth::user()->branch_id));
            }
            if ($request->filled('employee_id'))  $query->where('employee_id', $request->employee_id);
            if ($request->filled('date_from'))     $query->where('date', '>=', $request->date_from);
            if ($request->filled('date_to'))       $query->where('date', '<=', $request->date_to);
            if ($request->filled('status'))        $query->where('status', $request->status);
            if ($request->filled('branch_id')) {
                $query->whereHas('employee', fn($q) => $q->where('branch_id', $request->branch_id));
            }
            if ($request->filled('dept_id')) {
                $query->whereHas('employee', fn($q) => $q->where('department_id', $request->dept_id));
            }

            $rows = $query->get();
        }

        return view('attendance.short_leave.report', compact('rows', 'employees', 'branches', 'departments'));
    }
}
