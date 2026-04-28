<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = LeaveApplication::with(['employee.branch','employee.designation','leaveType','approver'])
            ->when(!$user->hasPermissionTo('view_all_branches'), fn($q) =>
                $q->whereHas('employee', fn($e) => $e->where('branch_id', $user->branch_id))
            );

        $tab   = $request->tab ?? 'pending';
        if ($tab !== 'all') $query->where('status', $tab);

        $leaves       = $query->latest()->paginate(20)->withQueryString();
        $pendingCount = LeaveApplication::where('status','pending')
            ->when(!$user->hasPermissionTo('view_all_branches'), fn($q) =>
                $q->whereHas('employee', fn($e) => $e->where('branch_id', $user->branch_id))
            )->count();
        $leaveTypes   = LeaveType::where('is_active',true)->get();
        $employees    = Employee::where('status','active')
            ->when(!$user->hasPermissionTo('view_all_branches'), fn($q) => $q->where('branch_id', $user->branch_id))
            ->get();

        return view('leaves.index', compact('leaves','pendingCount','leaveTypes','employees'));
    }

    public function apply(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'employee_id'  => 'required|exists:employees,id',
                'leave_type_id'=> 'required|exists:leave_types,id',
                'from_date'    => 'required|date',
                'to_date'      => 'required|date|after_or_equal:from_date',
                'reason'       => 'required|string',
            ]);

            $from  = Carbon::parse($request->from_date);
            $to    = Carbon::parse($request->to_date);
            $days  = 0;
            $cur   = $from->copy();
            while ($cur->lte($to)) {
                $isHoliday = Holiday::whereDate('date',$cur)->exists();
                $isWeekend = in_array($cur->dayOfWeek,[0,6]);
                if (!$isHoliday && !$isWeekend) $days++;
                $cur->addDay();
            }

            $docPath = null;
            if ($request->hasFile('document')) {
                $docPath = $request->file('document')->store('leave-documents','public');
            }

            LeaveApplication::create([
                'employee_id'   => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
                'from_date'     => $request->from_date,
                'to_date'       => $request->to_date,
                'total_days'    => $days,
                'reason'        => $request->reason,
                'document'      => $docPath,
                'status'        => 'pending',
                'bm_status'     => 'pending',
            ]);

            return redirect()->route('leaves.index')->with('success','Leave applied!');
        }

        $user       = Auth::user();
        if ($user->hasRole(['super-admin', 'hr-admin', 'hr', 'branch-manager']) || $user->hasPermissionTo('view_all_branches')) {
            $employees  = Employee::forUser($user)->where('status','active')->get();
        } else {
            $employees = $user->employee ? collect([$user->employee]) : collect();
        }
        $leaveTypes = LeaveType::where('is_active',true)->get();
        return view('leaves.apply', compact('employees','leaveTypes'));
    }

    public function approve(Request $request, LeaveApplication $leave)
    {
        $user   = Auth::user();
        $action = $request->action; // 'approve' or 'reject'
        $level  = $request->level;  // 'bm' or 'hr'

        if ($level === 'bm' && $user->hasRole(['branch-manager','hr-admin','super-admin'])) {
            if ($action === 'approve') {
                $leave->update(['bm_status'=>'approved','bm_approved_by'=>$user->id,'bm_approved_at'=>now(),'bm_remarks'=>$request->remarks]);
            } else {
                $leave->update(['bm_status'=>'rejected','status'=>'rejected','bm_approved_by'=>$user->id,'bm_approved_at'=>now(),'bm_remarks'=>$request->remarks]);
            }
        } elseif ($level === 'hr' && $user->hasRole(['hr','hr-admin','super-admin'])) {
            if ($action === 'approve' && $leave->bm_status === 'approved') {
                $leave->update(['status'=>'approved','approved_by'=>$user->id,'approved_at'=>now(),'remarks'=>$request->remarks]);
                // Mark attendance as leave
                $cur = Carbon::parse($leave->from_date);
                while ($cur->lte($leave->to_date)) {
                    \App\Models\Attendance::updateOrCreate(
                        ['employee_id'=>$leave->employee_id,'date'=>$cur->format('Y-m-d')],
                        ['status'=>'leave','source'=>'manual']
                    );
                    $cur->addDay();
                }
            } else {
                $leave->update(['status'=>'rejected','approved_by'=>$user->id,'approved_at'=>now(),'remarks'=>$request->remarks]);
            }
        }

        return back()->with('success','Action done.');
    }

    public function reject(Request $request, LeaveApplication $leave)
    {
        $user = Auth::user();
        $leave->update([
            'status'      => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'remarks'     => $request->remarks,
        ]);
        return back()->with('success', 'Leave rejected.');
    }

    public function destroy(LeaveApplication $leave)
    {
        $leave->update(['status' => 'cancelled']);
        return back()->with('success', 'Leave cancelled.');
    }


    // Staff: get own leave balance
    public function balance(Request $request)
    {
        $employee   = Employee::where('user_id', Auth::id())->firstOrFail();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $year       = $request->year ?? now()->year;

        $balances = $leaveTypes->map(function ($type) use ($employee, $year) {
            $used = LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->where('status','approved')
                ->whereYear('from_date', $year)->sum('total_days');
            return [
                'type'      => $type,
                'allowed'   => $type->days_per_year,
                'used'      => $used,
                'remaining' => max(0, $type->days_per_year - $used),
            ];
        });

        return view('leaves.balance', compact('balances','year','employee'));
    }
}
