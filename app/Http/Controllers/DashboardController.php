<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\ExtraPresentRequest;
use App\Models\LeaveApplication;
use App\Models\Notice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $user  = Auth::user();

        // Branch filter helper
        $branchId = (!$user->hasPermissionTo('view_all_branches') && $user->branch_id)
            ? $user->branch_id : null;

        // ── Employee Counts ────────────────────────────────────────────────
        $empQ = Employee::where('status', 'active');
        if ($branchId) $empQ->where('branch_id', $branchId);
        $totalEmployees = $empQ->count();
        $newThisMonth   = Employee::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereMonth('joining_date', $today->month)->whereYear('joining_date', $today->year)->count();

        // ── Today Attendance ───────────────────────────────────────────────
        $attQ = fn() => Attendance::whereDate('date', $today)
            ->when($branchId, fn($q) => $q->whereHas('employee', fn($e) => $e->where('branch_id', $branchId)));

        $presentToday = $attQ()->where('status', 'present')->count();
        $absentToday  = $attQ()->where('status', 'absent')->count();
        $lateToday    = $attQ()->where('status', 'late')->count();
        $onLeaveToday = $attQ()->where('status', 'leave')->count();

        // ── Pending Items ──────────────────────────────────────────────────
        $pendingLeaves = LeaveApplication::where('status', 'pending')->count();
        $pendingExtra  = ExtraPresentRequest::where('status', 'pending')->count();


        // ── Last 7 Days Chart ──────────────────────────────────────────────
        $chartLabels = $chartPresent = $chartAbsent = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $chartLabels[]  = $d->format('D d');
            $chartPresent[] = Attendance::whereDate('date', $d)->where('status', 'present')->count();
            $chartAbsent[]  = Attendance::whereDate('date', $d)->where('status', 'absent')->count();
        }

        // ── Department Distribution ────────────────────────────────────────
        $deptStats = Department::withCount('employees')->orderByDesc('employees_count')->take(8)->get();

        // ── Branches ──────────────────────────────────────────────────────
        $branches = Branch::withCount('employees')->where('is_active', true)->get();

        // ── Upcoming Birthdays ─────────────────────────────────────────────
        $birthdays = Employee::where('status', 'active')->whereNotNull('date_of_birth')->get()
            ->filter(function ($emp) {
                $bday = Carbon::parse($emp->date_of_birth)->setYear(now()->year);
                return $bday->isToday() || ($bday->isFuture() && $bday->diffInDays(now()) <= 7);
            })->take(5);

        // ── Latest Notices ─────────────────────────────────────────────────
        $notices = Notice::where('is_published', true)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', today()))
            ->latest('published_at')->take(5)->with('branch')->get();

        // ── Personal Staff Metrics ─────────────────────────────────────────
        $myPresents = 0;
        $myAbsents = 0;
        $myLeavesTaken = 0;
        $myPendingLeavesCount = 0;

        if ($user->employee) {
            $myPresents = Attendance::where('employee_id', $user->employee->id)
                ->whereMonth('date', $today->month)
                ->whereYear('date', $today->year)
                ->where('status', 'present')->count();

            $myAbsents = Attendance::where('employee_id', $user->employee->id)
                ->whereMonth('date', $today->month)
                ->whereYear('date', $today->year)
                ->where('status', 'absent')->count();

            $myLeavesTaken = LeaveApplication::where('employee_id', $user->employee->id)
                ->where('status', 'approved')
                ->whereYear('from_date', $today->year)
                ->sum('total_days');
                
            $myPendingLeavesCount = LeaveApplication::where('employee_id', $user->employee->id)
                ->where('status', 'pending')
                ->count();
        }

        return view('dashboard.index', compact(
            'today', 'totalEmployees', 'newThisMonth',
            'presentToday', 'absentToday', 'lateToday', 'onLeaveToday',
            'pendingLeaves', 'pendingExtra',
            'chartLabels', 'chartPresent', 'chartAbsent',
            'deptStats', 'branches', 'birthdays', 'notices',
            'myPresents', 'myAbsents', 'myLeavesTaken', 'myPendingLeavesCount'
        ));
    }
}
