<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Holiday;
use App\Models\LeaveType;
use App\Models\Notice;
use App\Models\Setting;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

// ── BranchController ──────────────────────────────────────────────────────────
class BranchController extends Controller
{
    public function index()    { return view('branches.index',   ['branches'    => Branch::withCount('employees','departments')->paginate(20)]); }
    public function create()   { return view('branches.create'); }
    public function store(Request $r)
    {
        $r->validate(['name'=>'required','code'=>'required|unique:branches,code']);
        $data = $r->except(['_token','logo']);
        if ($r->hasFile('logo')) $data['logo'] = $r->file('logo')->store('branch-logos','public');
        Branch::create($data);
        return redirect()->route('branches.index')->with('success','Branch created!');
    }
    public function edit(Branch $branch)   { return view('branches.edit',['branch'=>$branch]); }
    public function update(Request $r, Branch $branch)
    {
        $r->validate(['name'=>'required']);
        $data = $r->except(['_token','_method','logo']);
        if ($r->hasFile('logo')) { if($branch->logo) Storage::disk('public')->delete($branch->logo); $data['logo'] = $r->file('logo')->store('branch-logos','public'); }
        $branch->update($data);
        return redirect()->route('branches.index')->with('success','Branch updated!');
    }
    public function destroy(Branch $branch) { $branch->delete(); return back()->with('success','Deleted.'); }
}

// ── DepartmentController ──────────────────────────────────────────────────────
class DepartmentController extends Controller
{
    public function index()  { return view('departments.index',['departments'=>Department::with('branch')->withCount('employees','designations')->paginate(30)]); }
    public function store(Request $r)
    {
        $r->validate(['name'=>'required','branch_id'=>'required|exists:branches,id']);
        Department::create($r->only('name','code','description','branch_id','is_active'));
        return back()->with('success','Department created!');
    }
    public function update(Request $r, Department $department)
    {
        $department->update($r->only('name','code','description','branch_id','is_active'));
        return back()->with('success','Updated!');
    }
    public function destroy(Department $department) { $department->delete(); return back()->with('success','Deleted.'); }
}

// ── DesignationController ─────────────────────────────────────────────────────
class DesignationController extends Controller
{
    public function index()  { return view('designations.index',['designations'=>Designation::with('department.branch')->withCount('employees')->paginate(30)]); }
    public function store(Request $r)
    {
        $r->validate(['name'=>'required','department_id'=>'required|exists:departments,id']);
        Designation::create($r->only('name','grade','description','department_id','is_active'));
        return back()->with('success','Designation created!');
    }
    public function update(Request $r, Designation $designation)
    {
        $designation->update($r->only('name','grade','description','department_id','is_active'));
        return back()->with('success','Updated!');
    }
    public function destroy(Designation $designation) { $designation->delete(); return back()->with('success','Deleted.'); }
}

// ── ShiftController ───────────────────────────────────────────────────────────
class ShiftController extends Controller
{
    public function index()  { return view('shifts.index',['shifts'=>Shift::withCount('employees')->get()]); }
    public function store(Request $r)
    {
        $r->validate(['name'=>'required','start_time'=>'required','end_time'=>'required']);
        Shift::create(array_merge($r->only('name','start_time','end_time','grace_minutes','break_minutes','is_active'), [
            'sunday'   =>$r->boolean('sunday'),   'monday'   =>$r->boolean('monday'),
            'tuesday'  =>$r->boolean('tuesday'),  'wednesday'=>$r->boolean('wednesday'),
            'thursday' =>$r->boolean('thursday'), 'friday'   =>$r->boolean('friday'),
            'saturday' =>$r->boolean('saturday'),
        ]));
        return back()->with('success','Shift added!');
    }
    public function update(Request $r, Shift $shift)
    {
        $shift->update(array_merge($r->only('name','start_time','end_time','grace_minutes','break_minutes','is_active'), [
            'sunday'   =>$r->boolean('sunday'),   'monday'   =>$r->boolean('monday'),
            'tuesday'  =>$r->boolean('tuesday'),  'wednesday'=>$r->boolean('wednesday'),
            'thursday' =>$r->boolean('thursday'), 'friday'   =>$r->boolean('friday'),
            'saturday' =>$r->boolean('saturday'),
        ]));
        return back()->with('success','Shift updated!');
    }
    public function destroy(Shift $shift) { $shift->delete(); return back()->with('success','Deleted.'); }
}

// ── HolidayController ─────────────────────────────────────────────────────────
class HolidayController extends Controller
{
    public function index(Request $r)
    {
        $year = $r->year ?? now()->year;
        return view('holidays.index',['holidays'=>Holiday::whereYear('date',$year)->with('branch')->orderBy('date')->get(),'year'=>$year]);
    }
    public function store(Request $r)
    {
        $r->validate(['name'=>'required','date'=>'required|date']);
        Holiday::create($r->only('name','date','type','description','branch_id'));
        return back()->with('success','Holiday added!');
    }
    public function destroy(Holiday $holiday) { $holiday->delete(); return back()->with('success','Removed.'); }
}

// ── NoticeController ──────────────────────────────────────────────────────────
class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::with('creator','branch')->latest()->paginate(20);
        return view('notices.index', compact('notices'));
    }
    public function create() { return view('notices.create',['branches'=>Branch::all(),'departments'=>Department::all()]); }
    public function store(Request $r)
    {
        $r->validate(['title'=>'required','body'=>'required','type'=>'required']);
        Notice::create(array_merge($r->only('title','body','type','audience','branch_id','department_id','published_at','expires_at','is_published'), ['created_by'=>Auth::id()]));
        return redirect()->route('notices.index')->with('success','Notice published!');
    }
    public function edit(Notice $notice) { return view('notices.edit',['notice'=>$notice,'branches'=>Branch::all(),'departments'=>Department::all()]); }
    public function update(Request $r, Notice $notice)
    {
        $notice->update($r->only('title','body','type','audience','branch_id','department_id','published_at','expires_at','is_published'));
        return redirect()->route('notices.index')->with('success','Notice updated!');
    }
    public function destroy(Notice $notice) { $notice->delete(); return back()->with('success','Deleted.'); }
}

// ── SettingsController ────────────────────────────────────────────────────────
class SettingsController extends Controller
{
    public function general()
    {
        $settings = Setting::getGroup('general');
        return view('settings.general', compact('settings'));
    }
    public function updateGeneral(Request $r)
    {
        foreach (['company_name','company_tagline','company_address','company_phone','company_email','currency_symbol','currency_code'] as $key) {
            if ($r->has($key)) Setting::set($key, $r->$key, 'general');
        }
        if ($r->hasFile('company_logo')) {
            $path = $r->file('company_logo')->store('settings','public');
            Setting::set('company_logo', $path, 'general');
        }
        return back()->with('success','Settings saved!');
    }
    public function leave()
    {
        $leaveTypes = LeaveType::all();
        $settings   = Setting::getGroup('leave');
        return view('settings.leave', compact('leaveTypes','settings'));
    }
    public function updateLeave(Request $r)
    {
        Setting::set('carry_forward_leave', $r->carry_forward_leave ? '1' : '0', 'leave');
        Setting::set('leave_auto_carry',    $r->leave_auto_carry    ? '1' : '0', 'leave');
        return back()->with('success','Leave settings saved!');
    }
    public function storeLeaveType(Request $r)
    {
        $r->validate(['name'=>'required','days_per_year'=>'required|integer']);
        LeaveType::create($r->only('name','code','days_per_year','carry_forward','is_paid','color','is_active'));
        return back()->with('success','Leave type added!');
    }
    public function payroll()
    {
        $settings = Setting::getGroup('payroll');
        return view('settings.payroll', compact('settings'));
    }
    public function updatePayroll(Request $r)
    {
        foreach (['working_days_month','late_deduction','overtime_enabled','overtime_rate','eid_bonus_enabled'] as $key) {
            if ($r->has($key)) Setting::set($key, $r->$key, 'payroll');
        }
        return back()->with('success','Payroll settings saved!');
    }
}
