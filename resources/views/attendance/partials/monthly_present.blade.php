@php $periodLabel = \Carbon\Carbon::createFromDate($year,$mon,1)->format('F Y'); @endphp
<div style="margin-bottom:10px;text-align:center">
  <div style="font-weight:700;font-size:15px">MONTHLY PRESENT REPORT — {{ strtoupper($periodLabel) }}</div>
</div>
<table style="width:100%;border-collapse:collapse;font-size:11px">
  <thead>
    <tr>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px">SL</th>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px">Emp ID</th>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px;text-align:left">Name</th>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px">Branch</th>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px">Duty Days</th>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px">Present</th>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px">Late</th>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px">Half Day</th>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px">Absent</th>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px">Leave</th>
    </tr>
  </thead>
  <tbody>
    @foreach($employees as $i => $emp)
    @php
      $empAtt   = $allAtt->get($emp->id, collect());
      $we = 0; for($d=1;$d<=$daysInMonth;$d++){$day=\Carbon\Carbon::createFromDate($year,$mon,$d);if($day->isWeekend())$we++;}
      $dutyDays = $daysInMonth - $we - $totalHolidays;
      $present  = $empAtt->where('status','present')->count();
      $late     = $empAtt->where('status','late')->count();
      $half     = $empAtt->where('status','half_day')->count();
      $absent   = $empAtt->where('status','absent')->count();
      $leave    = $empAtt->where('status','leave')->count();
    @endphp
    <tr style="background:{{ $i%2==0?'#fff':'#f9fafb' }}">
      <td style="border:1px solid #ddd;padding:5px 8px;text-align:center">{{ $i+1 }}</td>
      <td style="border:1px solid #ddd;padding:5px 8px;font-size:10.5px">{{ $emp->employee_id }}</td>
      <td style="border:1px solid #ddd;padding:5px 8px;font-weight:600">{{ $emp->name }}</td>
      <td style="border:1px solid #ddd;padding:5px 8px;font-size:10.5px">{{ $emp->branch?->name ?? '—' }}</td>
      <td style="border:1px solid #ddd;padding:5px 8px;text-align:center">{{ $dutyDays }}</td>
      <td style="border:1px solid #ddd;padding:5px 8px;text-align:center;font-weight:700;color:#10b981">{{ $present+$late+$half }}</td>
      <td style="border:1px solid #ddd;padding:5px 8px;text-align:center;color:#f59e0b">{{ $late }}</td>
      <td style="border:1px solid #ddd;padding:5px 8px;text-align:center;color:#06b6d4">{{ $half }}</td>
      <td style="border:1px solid #ddd;padding:5px 8px;text-align:center;font-weight:700;color:#ef4444">{{ $absent }}</td>
      <td style="border:1px solid #ddd;padding:5px 8px;text-align:center;color:#6366f1">{{ $leave }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
