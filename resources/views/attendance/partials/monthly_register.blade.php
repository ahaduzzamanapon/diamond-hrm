@php $periodLabel = \Carbon\Carbon::createFromDate($year,$mon,1)->format('F Y'); @endphp
<div style="margin-bottom:10px;font-family:'Times New Roman',serif;text-align:center">
  <div style="font-weight:700;font-size:15px;letter-spacing:1px">ATTENDANTS REGISTER STATEMENT</div>
  <div style="font-size:12px;margin-top:3px">{{ $periodLabel }}</div>
</div>

@foreach($employees as $emp)
@php
  $empAtt = $allAtt->get($emp->id,collect())->keyBy(fn($a)=>$a->date->format('Y-m-d'));
@endphp
<div style="margin-bottom:18px;page-break-inside:avoid">
  <div style="background:#0a0a0a;color:#fff;padding:7px 10px;font-weight:700;display:flex;justify-content:space-between;font-size:12px">
    <span>{{ $emp->name }} ({{ $emp->employee_id }})</span>
    <span style="opacity:.75">{{ $emp->department?->name }} | {{ $emp->branch?->name }}</span>
  </div>
  <table style="width:100%;border-collapse:collapse;font-size:10.5px">
    <thead>
      <tr>
        <th style="background:#1a1a2e;color:#fff;padding:5px 7px;border:1px solid #444;text-align:center">Date</th>
        <th style="background:#1a1a2e;color:#fff;padding:5px 7px;border:1px solid #444;text-align:center">Day</th>
        <th colspan="4" style="background:#1f2937;color:#fff;padding:5px 7px;border:1px solid #444;text-align:center">Entrance Time</th>
        <th colspan="4" style="background:#374151;color:#fff;padding:5px 7px;border:1px solid #444;text-align:center">Exit Time</th>
        <th style="background:#1a1a2e;color:#fff;padding:5px 7px;border:1px solid #444;text-align:center">Status</th>
        <th style="background:#1a1a2e;color:#fff;padding:5px 7px;border:1px solid #444;min-width:60px">Remarks</th>
      </tr>
      <tr>
        <th colspan="2" style="background:#111827;color:#9ca3af;border:1px solid #444"></th>
        <th style="background:#1f2937;color:#d1d5db;padding:4px 6px;border:1px solid #444;font-size:9.5px;font-weight:500">Off. Time</th>
        <th style="background:#1f2937;color:#d1d5db;padding:4px 6px;border:1px solid #444;font-size:9.5px;font-weight:500">Ent. Time</th>
        <th style="background:#1f2937;color:#d1d5db;padding:4px 6px;border:1px solid #444;font-size:9.5px;font-weight:500">Device</th>
        <th style="background:#1f2937;color:#d1d5db;padding:4px 6px;border:1px solid #444;font-size:9.5px;font-weight:500">Diff.</th>
        <th style="background:#374151;color:#d1d5db;padding:4px 6px;border:1px solid #444;font-size:9.5px;font-weight:500">Off. Time</th>
        <th style="background:#374151;color:#d1d5db;padding:4px 6px;border:1px solid #444;font-size:9.5px;font-weight:500">Exit Time</th>
        <th style="background:#374151;color:#d1d5db;padding:4px 6px;border:1px solid #444;font-size:9.5px;font-weight:500">Device</th>
        <th style="background:#374151;color:#d1d5db;padding:4px 6px;border:1px solid #444;font-size:9.5px;font-weight:500">Diff.</th>
        <th colspan="2" style="background:#111827;color:#9ca3af;border:1px solid #444"></th>
      </tr>
    </thead>
    <tbody>
      @for($d=1; $d<=$daysInMonth; $d++)
        @php
          $day     = \Carbon\Carbon::createFromDate($year,$mon,$d);
          $key     = $day->format('Y-m-d');
          $att     = $empAtt->get($key);
          $holiday = $holidays->get($key);

          // Shift for this specific date — respects transfer history
          $shiftForDay = $emp->getShiftForDate($key);
          $dayName     = strtolower($day->format('l')); // 'monday','friday' etc.
          $isWknd      = $shiftForDay ? !(bool)($shiftForDay->$dayName) : $day->isWeekend();

          $status  = $att?->status ?? ($holiday?'Holiday':($isWknd?'Weekend':''));
          $offIn   = $shiftForDay ? \Carbon\Carbon::parse($shiftForDay->start_time)->format('h:i:s A') : '';
          $offOut  = $shiftForDay ? \Carbon\Carbon::parse($shiftForDay->end_time)->format('h:i:s A') : '';
          $actIn   = $att?->in_time  ? \Carbon\Carbon::parse($att->in_time)->format('h:i:s A') : '';
          $actOut  = $att?->out_time ? \Carbon\Carbon::parse($att->out_time)->format('h:i:s A') : '';

          // Device lookup from biometric logs
          $inLogKey  = $emp->id . '_' . $key . '_in';
          $outLogKey = $emp->id . '_' . $key . '_out';
          $inDevice  = '';
          $outDevice = '';
          if (isset($bioLogs) && $bioLogs->has($inLogKey)) {
              $inLog     = $bioLogs->get($inLogKey)->sortBy('punch_time')->first();
              $inDevice  = $inLog?->device?->name ?? ($inLog?->device_serial ?? '');
          }
          if (isset($bioLogs) && $bioLogs->has($outLogKey)) {
              $outLog    = $bioLogs->get($outLogKey)->sortByDesc('punch_time')->first();
              $outDevice = $outLog?->device?->name ?? ($outLog?->device_serial ?? '');
          }

          $diffIn  = '';
          if($emp->shift && $att?->in_time) {
            $mins = (int)$att->late_minutes;
            $diffIn = $mins>0 ? '<span style="color:#dc2626">+'.gmdate('H:i:s',$mins*60).'</span>' : '';
          }
          $diffOut = '';
          if($emp->shift && $att?->out_time) {
            $secs = \Carbon\Carbon::parse($emp->shift->end_time)->diffInSeconds(\Carbon\Carbon::parse($att->out_time), false);
            if($secs>0) $diffOut = '<span style="color:#dc2626">+'.gmdate('H:i:s',$secs).'</span>';
          }
          $rowBg = $isWknd||$holiday ? '#fef9ec' : ($status==='absent' ? '#fef2f2' : ($d%2==0?'#fff':'#f9fafb'));
          $statusColor = match($status){'Present','present'=>'#10b981','Absent','absent'=>'#ef4444','Late','late'=>'#f59e0b','Leave','leave'=>'#6366f1','Holiday'=>'#8b5cf6','Weekend'=>'#94a3b8',default=>'#0a0a0a'};
        @endphp
        <tr style="background:{{ $rowBg }}">
          <td style="border:1px solid #e5e7eb;padding:4px 7px;text-align:center;font-size:10.5px">{{ $day->format('d-M-y') }}</td>
          <td style="border:1px solid #e5e7eb;padding:4px 7px;text-align:center;font-size:10px;{{ $isWknd?'color:#e74c3c':'color:#374151' }}">{{ strtoupper($day->format('l')) }}</td>
          <td style="border:1px solid #e5e7eb;padding:4px 7px;text-align:center;color:#94a3b8;font-size:10px">{{ $offIn }}</td>
          <td style="border:1px solid #e5e7eb;padding:4px 7px;text-align:center;font-size:10px">{{ $actIn }}</td>
          <td style="border:1px solid #e5e7eb;padding:4px 7px;text-align:center;font-size:9px;color:#6366f1;max-width:70px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $inDevice }}">{{ $inDevice ?: '—' }}</td>
          <td style="border:1px solid #e5e7eb;padding:4px 7px;text-align:center;font-size:10px">{!! $diffIn !!}</td>
          <td style="border:1px solid #e5e7eb;padding:4px 7px;text-align:center;color:#94a3b8;font-size:10px">{{ $offOut }}</td>
          <td style="border:1px solid #e5e7eb;padding:4px 7px;text-align:center;font-size:10px">{{ $actOut }}</td>
          <td style="border:1px solid #e5e7eb;padding:4px 7px;text-align:center;font-size:9px;color:#10b981;max-width:70px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $outDevice }}">{{ $outDevice ?: '—' }}</td>
          <td style="border:1px solid #e5e7eb;padding:4px 7px;text-align:center;font-size:10px">{!! $diffOut !!}</td>
          <td style="border:1px solid #e5e7eb;padding:4px 7px;text-align:center;font-weight:700;color:{{ $statusColor }};font-size:10px">{{ $status }}</td>
          <td style="border:1px solid #e5e7eb;padding:4px 7px;font-size:9.5px;color:#94a3b8">{{ $att?->note ?? ($holiday ?? '') }}</td>
        </tr>
      @endfor
    </tbody>
  </table>
</div>
@endforeach
