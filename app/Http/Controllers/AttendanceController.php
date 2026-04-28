<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BiometricDevice;
use App\Models\BiometricLog;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\ExtraPresentRequest;
use App\Models\Holiday;
use App\Models\Setting;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    // ── Daily Report ───────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $user   = Auth::user();
        $date   = $request->date ? Carbon::parse($request->date) : Carbon::today();

        $query = Attendance::with(['employee.branch','employee.department','employee.designation','enteredBy'])
            ->whereDate('date', $date)
            ->whereHas('employee', function($q) use ($user, $request) {
                if (!$user->hasPermissionTo('view_all_branches')) $q->where('branch_id', $user->branch_id);
                if ($request->branch_id)     $q->where('branch_id',     $request->branch_id);
                if ($request->department_id) $q->where('department_id', $request->department_id);
                if ($request->designation_id)$q->where('designation_id',$request->designation_id);
                if ($request->status)        $q->where('status',        $request->status);
            });

        $attendances  = $query->orderBy('created_at','desc')->paginate(30)->withQueryString();
        $branches     = $user->hasPermissionTo('view_all_branches') ? Branch::all() : Branch::where('id',$user->branch_id)->get();
        $departments  = Department::when(!$user->hasPermissionTo('view_all_branches'), fn($q) => $q->where('branch_id',$user->branch_id))->get();
        $designations = Designation::all();

        // Summary counts for selected date
        $summary = Attendance::whereDate('date',$date)
            ->whereHas('employee', fn($q) => $user->hasPermissionTo('view_all_branches') ? $q : $q->where('branch_id',$user->branch_id))
            ->selectRaw('status, count(*) as count')->groupBy('status')
            ->pluck('count','status');

        return view('attendance.index', compact('attendances','date','summary','branches','departments','designations'));
    }

    // ── My Attendance ────────────────────────────────────────────────────────
    public function myAttendance(Request $request)
    {
        $user = Auth::user();
        if (!$user->employee) {
            return redirect()->back()->with('error', 'No employee profile linked.');
        }
        
        // Pass fake employee_ids array so individual report renders specifically for this user
        $request->merge(['employee_ids' => [$user->employee->id]]);
        return $this->individual($request);

    }

    // ── Individual Report (date range) ─────────────────────────────────────
    public function individual(Request $request)
    {
        $user     = Auth::user();
        $from     = $request->from ? Carbon::parse($request->from) : Carbon::today();
        $to       = $request->to   ? Carbon::parse($request->to)   : Carbon::today();
        $branches = $user->hasPermissionTo('view_all_branches') ? Branch::all() : Branch::where('id',$user->branch_id)->get();
        $departments  = Department::when(!$user->hasPermissionTo('view_all_branches'), fn($q)=>$q->where('branch_id',$user->branch_id))->get();
        $designations = Designation::all();
        $employees    = [];
        $records      = [];

        if ($request->filled('employee_ids')) {
            $empIds   = (array) $request->employee_ids;
            $employees = Employee::whereIn('id', $empIds)->with('designation','department')->get();

            foreach ($employees as $emp) {
                $att = Attendance::where('employee_id',$emp->id)
                    ->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
                    ->get()->keyBy(fn($a) => $a->date->format('Y-m-d'));

                // Build day-by-day record
                $days = [];
                $cur  = $from->copy();
                while ($cur->lte($to)) {
                    $days[$cur->format('Y-m-d')] = $att->get($cur->format('Y-m-d'));
                    $cur->addDay();
                }
                $records[$emp->id] = $days;
            }
        }

        return view('attendance.individual', compact('from','to','branches','departments','designations','employees','records'));
    }

    // ── Monthly Report (Summary Statement) ──────────────────────────────────
    public function monthly(Request $request)
    {
        $user   = Auth::user();
        $month  = $request->month ?? now()->format('Y-m');
        [$year,$mon] = explode('-', $month);

        $empQuery = Employee::with('designation','department')
            ->where('status','active')
            ->when(!$user->hasPermissionTo('view_all_branches'), fn($q) => $q->where('branch_id', $user->branch_id));
        if ($request->branch_id)     $empQuery->where('branch_id',    $request->branch_id);
        if ($request->department_id) $empQuery->where('department_id',$request->department_id);

        $employees   = $empQuery->get();
        $daysInMonth = Carbon::createFromDate($year, $mon, 1)->daysInMonth;
        $branches    = $user->hasPermissionTo('view_all_branches') ? Branch::all() : Branch::where('id',$user->branch_id)->get();
        $departments = Department::when(!$user->hasPermissionTo('view_all_branches'), fn($q)=>$q->where('branch_id',$user->branch_id))->get();

        // Load all attendance for the month at once
        $allAtt = Attendance::whereIn('employee_id', $employees->pluck('id'))
            ->whereYear('date', $year)->whereMonth('date', $mon)
            ->get()->groupBy('employee_id');

        $holidays      = Holiday::whereYear('date',$year)->whereMonth('date',$mon)->pluck('name','date');
        $totalHolidays = $holidays->count();

        return view('attendance.monthly', compact('month','year','mon','employees','daysInMonth','allAtt','holidays','totalHolidays','branches','departments'));
    }

    // ── Extra Present Requests ─────────────────────────────────────────────
    public function extraPresent(Request $request)
    {
        $user     = Auth::user();
        $requests = ExtraPresentRequest::with(['employee.branch','attendance','bmApprover','hrApprover'])
            ->when(!$user->hasPermissionTo('view_all_branches'), fn($q) =>
                $q->whereHas('employee', fn($eq) => $eq->where('branch_id', $user->branch_id))
            )
            ->latest()->paginate(20);

        return view('attendance.extra_present', compact('requests'));
    }

    public function approveExtra(Request $request, ExtraPresentRequest $extraRequest)
    {
        $user  = Auth::user();
        $level = $request->level; // 'bm' or 'hr'
        $action= $request->action; // 'approve' or 'reject'

        if ($action === 'approve') {
            if ($level === 'bm' && $user->hasRole(['branch-manager','hr-admin','super-admin'])) {
                $extraRequest->approveBM($user, $request->remarks ?? '');
            } elseif ($level === 'hr' && $user->hasRole(['hr','hr-admin','super-admin'])) {
                $extraRequest->approveHR($user, $request->remarks ?? '');
            }
        } else {
            $extraRequest->reject($user, $level, $request->remarks ?? '');
        }

        return back()->with('success','Action completed.');
    }

    // ── Manual Attendance Entry ────────────────────────────────────────────
    public function manual(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'date'        => 'required|date',
                'in_time'     => 'required',
                'status'      => 'required',
            ]);

            $employee = Employee::findOrFail($request->employee_id);
            $shift    = $employee->shift;
            $late     = 0;

            if ($shift && $request->in_time) {
                $shiftStart = Carbon::parse($request->date . ' ' . $shift->start_time);
                $inTime     = Carbon::parse($request->date . ' ' . $request->in_time);
                $late       = max(0, $inTime->diffInMinutes($shiftStart, false) * -1);
            }

            $att = Attendance::updateOrCreate(
                ['employee_id' => $request->employee_id, 'date' => $request->date],
                [
                    'in_time'    => $request->in_time,
                    'out_time'   => $request->out_time,
                    'status'     => $late > ($shift->grace_minutes ?? 0) ? 'late' : $request->status,
                    'late_minutes'=> $late,
                    'source'     => 'manual',
                    'note'       => $request->note,
                    'entered_by' => Auth::id(),
                ]
            );

            // Check if working on holiday/weekend — auto-create extra present request
            $holiday  = Holiday::whereDate('date', $request->date)->first();
            $dayOfWeek= Carbon::parse($request->date)->dayOfWeek; // 0=Sun, 6=Sat
            $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);

            if (($holiday || $isWeekend) && in_array($request->status, ['present','late'])) {
                ExtraPresentRequest::firstOrCreate(
                    ['employee_id' => $employee->id, 'date' => $request->date],
                    [
                        'attendance_id' => $att->id,
                        'day_type'      => $holiday ? 'holiday' : 'weekend',
                        'holiday_name'  => $holiday?->name,
                        'reason'        => $request->note,
                        'extra_pay'     => $employee->daily_rate,
                    ]
                );
            }

            return back()->with('success', 'Attendance saved!');
        }

        $employees = Employee::where('status','active')
            ->when(!Auth::user()->hasPermissionTo('view_all_branches'), fn($q) => $q->where('branch_id', Auth::user()->branch_id))
            ->get();
        return view('attendance.manual', compact('employees'));
    }

    // ── Update Attendance ─────────────────────────────────────────────────────
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'status' => 'required|in:present,absent,late,half_day,leave,holiday,weekend',
        ]);
        $data = ['status' => $request->status, 'note' => $request->note, 'entered_by' => Auth::id()];
        if ($request->check_in)  $data['in_time']  = $request->check_in;
        if ($request->check_out) $data['out_time']  = $request->check_out;
        $attendance->update($data);
        return back()->with('success','Attendance updated.');
    }

    // ── Report Page ───────────────────────────────────────────────────────────
    public function reportPage(Request $request)
    {
        $user = Auth::user();
        $branches    = Branch::where('is_active', true)->get();
        $departments = Department::when(!$user->hasPermissionTo('view_all_branches'), fn($q) => $q->where('branch_id', $user->branch_id))->get();
        $designations= Designation::all();
        $employees   = Employee::where('status','active')
            ->when(!$user->hasPermissionTo('view_all_branches'), fn($q) => $q->where('branch_id', $user->branch_id))
            ->with('branch','department')->orderBy('name')->get();
        return view('attendance.report', compact('branches','departments','designations','employees'));
    }

    // ── Report Data (AJAX) ────────────────────────────────────────────────────
    public function reportData(Request $request)
    {
        $type   = $request->type;
        $mode   = $request->mode ?? 'date';
        $date1  = $request->date1 ?: today()->format('Y-m-d');
        $date2  = ($request->date2 && $request->date2 !== '') ? $request->date2 : $date1;
        $month  = $request->month ?: now()->format('Y-m');
        $empIds = $request->employee_ids ?? [];
        [$year, $mon] = explode('-', $month);

        // Base employee set
        $user = Auth::user();
        $empQuery = Employee::where('status','active')
            ->when(!$user->hasPermissionTo('view_all_branches'), fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->dept_id,   fn($q) => $q->where('department_id', $request->dept_id))
            ->when($empIds,             fn($q) => $q->whereIn('id', $empIds))
            ->with('branch','department','designation','shift');
        $employees = $empQuery->get();

        if ($employees->isEmpty()) {
            return response()->json(['html' => '<div class="empty-state"><div class="empty-icon">👥</div><h3>No employees match your selection</h3></div>']);
        }

        // Monthly modes
        if (in_array($type, ['monthly_summary','monthly_register','monthly_present','monthly_absent'])) {
            $daysInMonth = Carbon::createFromDate($year, $mon, 1)->daysInMonth;
            $allAtt = Attendance::whereIn('employee_id', $employees->pluck('id'))
                ->whereYear('date', $year)->whereMonth('date', $mon)
                ->get()->groupBy('employee_id');
            $holidays = Holiday::whereYear('date',$year)->whereMonth('date',$mon)->pluck('name','date');
            $totalHolidays = $holidays->count();

            // For register: load biometric logs grouped by emp_date_punchtype for device name
            $bioLogs = collect();
            if ($type === 'monthly_register') {
                $bioLogs = \App\Models\BiometricLog::with('device')
                    ->whereIn('employee_id', $employees->pluck('id'))
                    ->whereYear('punch_time', $year)
                    ->whereMonth('punch_time', $mon)
                    ->get()
                    ->groupBy(fn($log) =>
                        $log->employee_id . '_' .
                        \Carbon\Carbon::parse($log->punch_time)->format('Y-m-d') . '_' .
                        $log->punch_type
                    );
            }

            $html = view('attendance.partials.'.$type, compact(
                'employees','allAtt','daysInMonth','holidays','totalHolidays','year','mon','month','bioLogs'
            ))->render();
            return response()->json(['html' => $html]);
        }


        // Date / Range modes
        $attQuery = Attendance::with(['employee.branch','employee.department','employee.designation'])
            ->whereBetween('date', [$date1, $date2])
            ->whereIn('employee_id', $employees->pluck('id'));

        if ($type === 'present')  $attQuery->whereIn('status',['present','late','half_day']);
        if ($type === 'absent')   $attQuery->where('status','absent');
        if ($type === 'late')     $attQuery->where('status','late');
        if ($type === 'early_out')$attQuery->where('early_out_minutes','>',0);
        if ($type === 'leave')    $attQuery->where('status','leave');

        $records = $attQuery->orderBy('date')->orderBy('employee_id')->get();

        // Continuous: group by employee
        if (in_array($type, ['continuous','performance','late_analysis'])) {
            $grouped = $records->groupBy('employee_id');
            $date1C = Carbon::parse($date1); $date2C = Carbon::parse($date2);
            $html = view('attendance.partials.continuous', compact('employees','grouped','date1C','date2C','type'))->render();
        } else {
            $html = view('attendance.partials.daily_table', compact('records','type','date1','date2'))->render();
        }
        return response()->json(['html' => $html]);
    }

    // ── Process Attendance (auto-fill absent/weekend/holiday) ─────────────────
    public function processAttendance(Request $request)
    {
        $date1  = $request->date1 ?: today()->format('Y-m-d');
        $date2  = ($request->date2 && $request->date2 !== '') ? $request->date2 : $date1;
        $empIds = $request->employee_ids ?? [];
        $user   = Auth::user();

        // Build employee set
        $employees = Employee::where('status', 'active')
            ->when(!$user->hasPermissionTo('view_all_branches'), fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->dept_id,   fn($q) => $q->where('department_id', $request->dept_id))
            ->when($empIds,             fn($q) => $q->whereIn('id', $empIds))
            ->with(['shift', 'transfers.fromShift', 'transfers.toShift'])
            ->get();

        if ($employees->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No employees found matching criteria.']);
        }
        $empIdsList = $employees->pluck('id');

        $start   = Carbon::parse($date1);
        $end     = Carbon::parse($date2);
        $created = 0;
        $updated = 0;
        $skipped = 0;

        // Preload holidays for range
        $holidays = Holiday::whereBetween('date', [$date1, $date2])
            ->pluck('name', 'date')
            ->mapWithKeys(fn($name, $d) => [Carbon::parse($d)->format('Y-m-d') => $name]);

        // Preload existing attendance to avoid N+1
        $existing = Attendance::whereIn('employee_id', $empIdsList)
            ->whereBetween('date', [$date1, $date2])
            ->get()
            ->groupBy(fn($a) => $a->employee_id . '_' . $a->date->format('Y-m-d'));

        $toInsert = [];
        $now      = now()->format('Y-m-d H:i:s');

        $cur = $start->copy();
        while ($cur->lte($end)) {
            $dateStr   = $cur->format('Y-m-d');
            $isWeekend = $cur->isWeekend();
            $holiday   = $holidays->get($dateStr);
            $baseStatus= match(true) {
                !is_null($holiday) => 'holiday',
                $isWeekend         => 'weekend',
                default            => 'absent',
            };

            foreach ($employees as $emp) {
                $key = $emp->id . '_' . $dateStr;
                
                if ($existing->has($key)) {
                    $att = $existing->get($key)->first();

                    // Never touch leave records
                    if ($att->status === 'leave') {
                        $skipped++;
                        continue;
                    }

                    if ($att->in_time || $att->out_time) {
                        // Has punch data — use shift for that specific date (respects transfers)
                        $status = 'present';
                        $late   = 0;
                        $shiftForDate = $emp->getShiftForDate($dateStr);
                        if ($att->in_time && $shiftForDate) {
                            $shiftStart = Carbon::parse($dateStr . ' ' . $shiftForDate->start_time);
                            $inTime     = Carbon::parse($dateStr . ' ' . $att->in_time);
                            $late       = max(0, $inTime->diffInMinutes($shiftStart, false) * -1);
                            if ($late > ($shiftForDate->grace_minutes ?? 0)) {
                                $status = 'late';
                            }
                        }
                        $att->update(['status' => $status, 'late_minutes' => $late, 'entered_by' => $user->id]);
                        $updated++;
                    } else {
                        // No punch — force to holiday/weekend/absent
                        $att->update([
                            'status'     => $baseStatus,
                            'note'       => $baseStatus === 'holiday' ? $holiday : $att->note,
                            'entered_by' => $user->id,
                        ]);
                        $updated++;
                    }
                } else {
                    $toInsert[] = [
                        'employee_id' => $emp->id,
                        'date'        => $dateStr,
                        'status'      => $baseStatus,
                        'source'      => 'manual',
                        'note'        => $baseStatus === 'holiday' ? $holiday : null,
                        'entered_by'  => $user->id,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                    $created++;
                }
            }

            $cur->addDay();
        }

        // Bulk insert in chunks
        if (!empty($toInsert)) {
            foreach (array_chunk($toInsert, 200) as $chunk) {
                Attendance::insert($chunk);
            }
        }

        $days = $start->diffInDays($end) + 1;
        return response()->json([
            'success' => true,
            'message' => "Processed {$days} day(s) for {$employees->count()} employee(s). Created: {$created}, Updated: {$updated}, Skipped: {$skipped}.",
        ]);
    }
}
