@php
  $start = $date1C->format('d-M-y');
  $end   = $date2C->format('d-M-y');
  $statusColor = [
    'present'=>'#10b981','absent'=>'#ef4444','late'=>'#f59e0b',
    'half_day'=>'#06b6d4','leave'=>'#6366f1','holiday'=>'#8b5cf6',
    'weekend'=>'#94a3b8',
  ];
  $fmtMin = fn($m) => floor($m/60).'h '.($m%60).'m';
@endphp

<div style="margin-bottom:12px;font-family:'Times New Roman',serif;text-align:center">
  <div style="font-weight:700;font-size:15px;letter-spacing:1px">
    @if($type==='performance')ATTENDANCE PERFORMANCE REPORT
    @elseif($type==='late_analysis')LATE ANALYSIS REPORT
    @else ATTENDANCE REGISTER — CONTINUOUS
    @endif
  </div>
  <div style="font-size:12px;margin-top:4px">{{ $start }} to {{ $end }}</div>
</div>

{{-- ─── PERFORMANCE SUMMARY ───────────────────────────────────────── --}}
@if($type === 'performance')
<table style="width:100%;border-collapse:collapse;font-size:11px">
  <thead>
    <tr style="background:#1a1a2e;color:#fff">
      <th style="padding:7px 10px;border:1px solid #444;text-align:left">#</th>
      <th style="padding:7px 10px;border:1px solid #444;text-align:left">Employee</th>
      <th style="padding:7px 10px;border:1px solid #444;text-align:left">Dept / Branch</th>
      <th style="padding:7px 10px;border:1px solid #444;text-align:center">Work Days</th>
      <th style="padding:7px 10px;border:1px solid #444;text-align:center">Present</th>
      <th style="padding:7px 10px;border:1px solid #444;text-align:center">Absent</th>
      <th style="padding:7px 10px;border:1px solid #444;text-align:center">Late Days</th>
      <th style="padding:7px 10px;border:1px solid #444;text-align:center">Total Late</th>
      <th style="padding:7px 10px;border:1px solid #444;text-align:center">Leave</th>
      <th style="padding:7px 10px;border:1px solid #444;text-align:center">Att. %</th>
    </tr>
  </thead>
  <tbody>
    @foreach($employees as $i => $emp)
    @php
      $recs     = $grouped->get($emp->id, collect());
      $present  = $recs->whereIn('status',['present','late','half_day'])->count();
      $absent   = $recs->where('status','absent')->count();
      $lateDays = $recs->where('status','late')->count();
      $totLate  = $recs->sum('late_minutes');
      $leave    = $recs->where('status','leave')->count();
      $workDays = $recs->whereNotIn('status',['weekend','holiday'])->count();
      $attPct   = $workDays > 0 ? round(($present/$workDays)*100,1) : 0;
      $pctColor = $attPct>=90 ? '#10b981' : ($attPct>=75 ? '#f59e0b' : '#ef4444');
      $bg       = $i%2===0 ? '#fff' : '#f9fafb';
    @endphp
    <tr style="background:{{ $bg }}">
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center">{{ $i+1 }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;font-weight:600">
        {{ $emp->name }}<br><span style="font-size:10px;color:#64748b;font-weight:400">{{ $emp->employee_id }}</span>
      </td>
      <td style="border:1px solid #ddd;padding:5px 9px;font-size:10px;color:#475569">
        {{ $emp->department?->name ?? '—' }}<br>{{ $emp->branch?->name ?? '—' }}
      </td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center">{{ $workDays }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center;color:#10b981;font-weight:700">{{ $present }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center;color:#ef4444;font-weight:700">{{ $absent }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center;color:#f59e0b;font-weight:700">{{ $lateDays }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center;color:#dc2626;font-size:10px">
        {{ $totLate > 0 ? $fmtMin($totLate) : '—' }}
      </td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center;color:#6366f1">{{ $leave }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center;font-weight:700;color:{{ $pctColor }}">
        {{ $attPct }}%
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

{{-- ─── LATE ANALYSIS ──────────────────────────────────────────────── --}}
@elseif($type === 'late_analysis')
@foreach($employees as $emp)
@php
  $lateRecs = $grouped->get($emp->id, collect())->where('status','late')->sortBy('date');
  if($lateRecs->isEmpty()) continue;
  $totLate = $lateRecs->sum('late_minutes');
@endphp
<div style="margin-bottom:20px">
  <div style="background:#0a0a0a;color:#fff;padding:8px 12px;border-radius:6px 6px 0 0;font-weight:700;display:flex;justify-content:space-between;align-items:center">
    <span>{{ $emp->name }} &nbsp;<span style="font-size:11px;opacity:0.7">({{ $emp->employee_id }})</span></span>
    <span style="font-size:11px">{{ $emp->department?->name }} | {{ $emp->branch?->name }}</span>
  </div>
  <table style="width:100%;border-collapse:collapse;font-size:11px">
    <thead>
      <tr style="background:#1a1a2e;color:#fff">
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Date</th>
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Day</th>
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Official In</th>
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Actual In</th>
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Late (min)</th>
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Late (h:m)</th>
        <th style="padding:6px 8px;border:1px solid #444">Note</th>
      </tr>
    </thead>
    <tbody>
      @foreach($lateRecs as $i => $att)
      @php
        $offIn  = $emp->shift ? \Carbon\Carbon::parse($emp->shift->start_time)->format('h:i A') : '—';
        $actIn  = $att->in_time ? \Carbon\Carbon::parse($att->in_time)->format('h:i A') : '—';
        $latMin = $att->late_minutes ?? 0;
        $rowBg  = $i%2===0 ? '#fff' : '#fff5f5';
      @endphp
      <tr style="background:{{ $rowBg }}">
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center">{{ $att->date->format('d-M-y') }}</td>
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center">{{ $att->date->format('D') }}</td>
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center;color:#64748b">{{ $offIn }}</td>
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center;color:#dc2626;font-weight:600">{{ $actIn }}</td>
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center;color:#dc2626;font-weight:700">+{{ $latMin }}</td>
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center;color:#dc2626">
          {{ $latMin > 0 ? $fmtMin($latMin) : '—' }}
        </td>
        <td style="border:1px solid #ddd;padding:4px 7px;font-size:10px;color:#64748b">{{ $att->note }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr style="background:#fef2f2;font-weight:700">
        <td colspan="4" style="border:1px solid #ddd;padding:6px 8px">Total Late Occurrences: {{ $lateRecs->count() }}</td>
        <td style="border:1px solid #ddd;padding:6px 8px;text-align:center;color:#dc2626">+{{ $totLate }}</td>
        <td style="border:1px solid #ddd;padding:6px 8px;text-align:center;color:#dc2626">{{ $fmtMin($totLate) }}</td>
        <td style="border:1px solid #ddd;padding:6px 8px"></td>
      </tr>
    </tfoot>
  </table>
</div>
@endforeach

{{-- ─── FULL REGISTER ──────────────────────────────────────────────── --}}
@else
@foreach($employees as $emp)
@php
  $records = $grouped->get($emp->id, collect());
  if($records->isEmpty()) continue;
  $present  = $records->whereIn('status',['present','late','half_day'])->count();
  $absent   = $records->where('status','absent')->count();
  $late     = $records->where('status','late')->count();
  $leave    = $records->where('status','leave')->count();
  $totLate  = $records->sum('late_minutes');
@endphp
<div style="margin-bottom:20px">
  <div style="background:#0a0a0a;color:#fff;padding:8px 12px;border-radius:6px 6px 0 0;font-weight:700;display:flex;justify-content:space-between;align-items:center">
    <span>{{ $emp->name }} &nbsp;<span style="font-size:11px;opacity:0.7">({{ $emp->employee_id }})</span></span>
    <span style="font-size:11px">{{ $emp->department?->name }} | {{ $emp->branch?->name }}</span>
  </div>
  <table style="width:100%;border-collapse:collapse;font-size:11px">
    <thead>
      <tr style="background:#1a1a2e;color:#fff">
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Date</th>
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Day</th>
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Off. In</th>
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Act. In</th>
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Late</th>
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Off. Out</th>
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Act. Out</th>
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Early Out</th>
        <th style="padding:6px 8px;border:1px solid #444;text-align:center">Status</th>
        <th style="padding:6px 8px;border:1px solid #444">Remarks</th>
      </tr>
    </thead>
    <tbody>
      @foreach($records->sortBy('date') as $i => $att)
      @php
        $latMin   = $att->late_minutes ?? 0;
        $earlyMin = $att->early_out_minutes ?? 0;
        $offIn    = $emp->shift ? \Carbon\Carbon::parse($emp->shift->start_time)->format('h:i A') : '—';
        $offOut   = $emp->shift ? \Carbon\Carbon::parse($emp->shift->end_time)->format('h:i A') : '—';
        $actIn    = $att->in_time  ? \Carbon\Carbon::parse($att->in_time)->format('h:i A')  : '—';
        $actOut   = $att->out_time ? \Carbon\Carbon::parse($att->out_time)->format('h:i A') : '—';
        $sc       = $statusColor[$att->status] ?? '#0a0a0a';
        $rowBg    = in_array($att->status,['weekend','holiday']) ? '#f8fafc' : ($i%2===0 ? '#fff' : '#fafafa');
      @endphp
      <tr style="background:{{ $rowBg }}">
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center">{{ $att->date->format('d-M-y') }}</td>
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center">{{ $att->date->format('D') }}</td>
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center;color:#64748b">{{ $offIn }}</td>
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center{{ $latMin>0 ? ';color:#f59e0b;font-weight:600' : '' }}">
          {{ $actIn }}
        </td>
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center;font-size:10px;color:{{ $latMin>0 ? '#dc2626' : '#94a3b8' }}">
          {{ $latMin>0 ? '+'.$fmtMin($latMin) : '' }}
        </td>
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center;color:#64748b">{{ $offOut }}</td>
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center">{{ $actOut }}</td>
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center;font-size:10px;color:{{ $earlyMin>0 ? '#dc2626' : '#94a3b8' }}">
          {{ $earlyMin>0 ? '-'.$fmtMin($earlyMin) : '' }}
        </td>
        <td style="border:1px solid #ddd;padding:4px 7px;text-align:center;font-weight:700;color:{{ $sc }}">
          {{ ucfirst(str_replace('_',' ',$att->status)) }}
        </td>
        <td style="border:1px solid #ddd;padding:4px 7px;font-size:10px;color:#64748b">{{ $att->note }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr style="background:#f1f5f9;font-weight:700;font-size:12px">
        <td colspan="2" style="border:1px solid #ddd;padding:6px 8px">Total: {{ $records->count() }}</td>
        <td colspan="3" style="border:1px solid #ddd;padding:6px 8px;text-align:center;color:#10b981">Present: {{ $present }}</td>
        <td colspan="1" style="border:1px solid #ddd;padding:6px 8px;text-align:center;color:#ef4444">Absent: {{ $absent }}</td>
        <td colspan="1" style="border:1px solid #ddd;padding:6px 8px;text-align:center;color:#f59e0b">Late: {{ $late }}</td>
        <td colspan="1" style="border:1px solid #ddd;padding:6px 8px;text-align:center;color:#6366f1">Leave: {{ $leave }}</td>
        <td colspan="2" style="border:1px solid #ddd;padding:6px 8px;text-align:center">
          Tot. Late: {{ $fmtMin($totLate) }}
        </td>
      </tr>
    </tfoot>
  </table>
</div>
@endforeach
@endif
