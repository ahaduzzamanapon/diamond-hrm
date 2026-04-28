@php
  $from = $date1; $to = $date2;
  $periodLabel = ($date1 === $date2) ? \Carbon\Carbon::parse($date1)->format('d M Y') : \Carbon\Carbon::parse($date1)->format('d M Y').' — '.\Carbon\Carbon::parse($date2)->format('d M Y');
@endphp
<div style="margin-bottom:12px;font-family:'Times New Roman',serif;text-align:center">
  <div style="font-weight:700;font-size:15px;letter-spacing:1px">ATTENDANCE REPORT — {{ strtoupper(str_replace('_',' ',$type)) }}</div>
  <div style="font-size:12px;margin-top:4px">{{ $periodLabel }}</div>
</div>

<table style="width:100%;border-collapse:collapse;font-size:11.5px">
  <thead>
    <tr>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555;text-align:left">SL</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555">Emp ID</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555;text-align:left;min-width:130px">Name</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555">Branch</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555">Dept.</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555">Date</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555">Day</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555">In Time</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555">Out Time</th>
      @if($type === 'late')<th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555">Late Mins</th>@endif
      @if($type === 'early_out')<th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555">Early Out Mins</th>@endif
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555">Status</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555;min-width:80px">Note</th>
    </tr>
  </thead>
  <tbody>
    @forelse($records as $i => $att)
    <tr style="background:{{ $i%2==0?'#fff':'#f9fafb' }}">
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center">{{ $i+1 }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center;font-size:11px">{{ $att->employee->employee_id }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;font-weight:600">{{ $att->employee->name }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;font-size:11px">{{ $att->employee->branch?->name ?? '—' }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;font-size:11px">{{ $att->employee->department?->name ?? '—' }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center">{{ $att->date->format('d-M-y') }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center;font-size:11px">{{ $att->date->format('D') }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center">{{ $att->in_time ? \Carbon\Carbon::parse($att->in_time)->format('h:i A') : '—' }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center">{{ $att->out_time ? \Carbon\Carbon::parse($att->out_time)->format('h:i A') : '—' }}</td>
      @if($type === 'late')<td style="border:1px solid #ddd;padding:5px 9px;text-align:center;color:#f59e0b;font-weight:600">{{ $att->late_minutes }}</td>@endif
      @if($type === 'early_out')<td style="border:1px solid #ddd;padding:5px 9px;text-align:center;color:#ef4444;font-weight:600">{{ $att->early_out_minutes }}</td>@endif
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center">
        <span style="font-weight:700;color:{{ match($att->status){'present'=>'#10b981','absent'=>'#ef4444','late'=>'#f59e0b','half_day'=>'#06b6d4','leave'=>'#6366f1','holiday'=>'#8b5cf6','weekend'=>'#94a3b8',default=>'#0a0a0a'} }}">
          {{ ucfirst(str_replace('_',' ',$att->status)) }}
        </span>
      </td>
      <td style="border:1px solid #ddd;padding:5px 9px;font-size:11px;color:#64748b">{{ $att->note }}</td>
    </tr>
    @empty
    <tr><td colspan="12" style="text-align:center;padding:30px;color:#94a3b8">No records found for the selected criteria</td></tr>
    @endforelse
  </tbody>
  @if($records->count())
  <tfoot>
    <tr style="background:#f1f5f9;font-weight:700">
      <td colspan="5" style="border:1px solid #ddd;padding:7px 9px">Total: {{ $records->count() }} records</td>
      <td colspan="{{ $type==='late'||$type==='early_out' ? 7 : 6 }}" style="border:1px solid #ddd;padding:7px 9px;text-align:right">
        @foreach($records->groupBy('status') as $st => $group)
          {{ ucfirst($st) }}: {{ $group->count() }} &nbsp;
        @endforeach
      </td>
    </tr>
  </tfoot>
  @endif
</table>
