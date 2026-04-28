@php
  $periodLabel = \Carbon\Carbon::createFromDate($year,$mon,1)->format('F Y');
@endphp
<div style="margin-bottom:10px;font-family:'Times New Roman',serif;text-align:center">
  <div style="font-weight:700;font-size:15px;letter-spacing:1px">ATTENDANTS SUMMARY STATEMENT</div>
  <div style="font-size:12px;margin-top:3px">For the Date of {{ \Carbon\Carbon::createFromDate($year,$mon,1)->format('01-M-y') }} To {{ \Carbon\Carbon::createFromDate($year,$mon,$daysInMonth)->format('d-M-y') }}</div>
</div>
<table style="width:100%;border-collapse:collapse;font-size:10.5px">
  <thead>
    <tr>
      <th rowspan="2" style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:5px 7px">SL</th>
      <th rowspan="2" style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:5px 7px">Emp ID</th>
      <th rowspan="2" style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:5px 7px;min-width:120px">Name</th>
      <th rowspan="2" style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:5px 7px">Duty Day</th>
      <th rowspan="2" style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:5px 7px">Present</th>
      <th rowspan="2" style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:5px 7px">Absent</th>
      <th colspan="2" style="background:#1a1a1a;color:#fff;border:1px solid #555;padding:5px 7px;text-align:center">Weekend</th>
      <th colspan="3" style="background:#1a1a1a;color:#fff;border:1px solid #555;padding:5px 7px;text-align:center">Leave</th>
      <th rowspan="2" style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:5px 7px">Late</th>
      <th rowspan="2" style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:5px 7px">Lat Abst</th>
      <th rowspan="2" style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:5px 7px">Ov. Late</th>
      <th rowspan="2" style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:5px 7px">Pnd.Lev</th>
      <th rowspan="2" style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:5px 7px">Net Paid</th>
      <th rowspan="2" style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:5px 7px;min-width:60px">Remarks</th>
      <th rowspan="2" style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:5px 7px;min-width:50px">App.M.D.</th>
    </tr>
    <tr>
      <th style="background:#222;color:#fff;border:1px solid #555;padding:4px 6px;font-size:10px">W.E.</th>
      <th style="background:#222;color:#fff;border:1px solid #555;padding:4px 6px;font-size:10px">W.W.</th>
      <th style="background:#222;color:#fff;border:1px solid #555;padding:4px 6px;font-size:10px">AP</th>
      <th style="background:#222;color:#fff;border:1px solid #555;padding:4px 6px;font-size:10px">FL</th>
      <th style="background:#222;color:#fff;border:1px solid #555;padding:4px 6px;font-size:10px">SL</th>
    </tr>
  </thead>
  <tbody>
    @foreach($employees as $i => $emp)
    @php
      $empAtt   = $allAtt->get($emp->id, collect());
      $we = 0;
      for($d=1;$d<=$daysInMonth;$d++){$day=\Carbon\Carbon::createFromDate($year,$mon,$d);if($day->isWeekend())$we++;}
      $ww       = $empAtt->where('status','extra_present')->count();
      $present  = $empAtt->whereIn('status',['present','late','half_day'])->count() + $ww;
      $absent   = $empAtt->where('status','absent')->count();
      $apLeave  = \App\Models\LeaveApplication::where('employee_id',$emp->id)->where('status','approved')
                    ->whereYear('from_date',$year)->whereMonth('from_date',$mon)->sum('total_days');
      $late     = $empAtt->where('status','late')->count();
      $latAbst  = max(0,$absent);
      $ovLate   = $empAtt->where('late_minutes','>',60)->count();
      $pndLev   = \App\Models\LeaveApplication::where('employee_id',$emp->id)->where('status','pending')->count();
      $dutyDays = $daysInMonth - $we - $totalHolidays;
      $netPaid  = max(0, $present);
    @endphp
    <tr style="background:{{ $i%2==0?'#fff':'#f9fafb' }}">
      <td style="border:1px solid #ddd;padding:4px 6px;text-align:center">{{ $i+1 }}</td>
      <td style="border:1px solid #ddd;padding:4px 6px;font-size:10px">{{ $emp->employee_id }}</td>
      <td style="border:1px solid #ddd;padding:4px 6px;font-weight:600">{{ $emp->name }}</td>
      <td style="border:1px solid #ddd;padding:4px 6px;text-align:center">{{ $dutyDays }}</td>
      <td style="border:1px solid #ddd;padding:4px 6px;text-align:center;font-weight:700;color:#10b981">{{ $present }}</td>
      <td style="border:1px solid #ddd;padding:4px 6px;text-align:center;font-weight:700;color:#ef4444">{{ $absent }}</td>
      <td style="border:1px solid #ddd;padding:4px 6px;text-align:center">{{ $we }}</td>
      <td style="border:1px solid #ddd;padding:4px 6px;text-align:center">{{ $ww }}</td>
      <td style="border:1px solid #ddd;padding:4px 6px;text-align:center">{{ $apLeave }}</td>
      <td style="border:1px solid #ddd;padding:4px 6px;text-align:center">0</td>
      <td style="border:1px solid #ddd;padding:4px 6px;text-align:center">0</td>
      <td style="border:1px solid #ddd;padding:4px 6px;text-align:center;color:#f59e0b">{{ $late }}</td>
      <td style="border:1px solid #ddd;padding:4px 6px;text-align:center">{{ $latAbst }}</td>
      <td style="border:1px solid #ddd;padding:4px 6px;text-align:center">{{ $ovLate }}</td>
      <td style="border:1px solid #ddd;padding:4px 6px;text-align:center;color:{{ $pndLev?'#f59e0b':'#94a3b8' }}">{{ $pndLev }}</td>
      <td style="border:1px solid #ddd;padding:4px 6px;text-align:center;font-weight:700">{{ $netPaid }}</td>
      <td style="border:1px solid #ddd;padding:4px 6px"></td>
      <td style="border:1px solid #ddd;padding:4px 6px"></td>
    </tr>
    @endforeach
  </tbody>
</table>
<div style="margin-top:8px;font-size:10px;color:#64748b">
  <strong>Legend:</strong> W.E.=Weekend, W.W.=Weekend Work, AP=Annual/Privileged Leave, FL=Festival Leave, SL=Sick Leave, Lat Abst=Late Absent, Ov. Late=Over Late (&gt;60 min), Pnd.Lev=Pending Leave
</div>
