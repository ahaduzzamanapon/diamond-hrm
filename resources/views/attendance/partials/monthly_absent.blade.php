@php $periodLabel = \Carbon\Carbon::createFromDate($year,$mon,1)->format('F Y'); @endphp
<div style="margin-bottom:10px;text-align:center">
  <div style="font-weight:700;font-size:15px">MONTHLY ABSENT REPORT — {{ strtoupper($periodLabel) }}</div>
</div>
<table style="width:100%;border-collapse:collapse;font-size:11px">
  <thead>
    <tr>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px">SL</th>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px">Emp ID</th>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px;text-align:left">Name</th>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px">Branch</th>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px">Absent Days</th>
      <th style="background:#0a0a0a;color:#fff;border:1px solid #555;padding:6px 9px">Absent Dates</th>
    </tr>
  </thead>
  <tbody>
    @foreach($employees as $i => $emp)
    @php
      $empAtt  = $allAtt->get($emp->id, collect());
      $absents = $empAtt->where('status','absent');
      if($absents->isEmpty()) continue;
    @endphp
    <tr style="background:{{ $i%2==0?'#fff':'#f9fafb' }}">
      <td style="border:1px solid #ddd;padding:5px 8px;text-align:center">{{ $i+1 }}</td>
      <td style="border:1px solid #ddd;padding:5px 8px;font-size:10.5px">{{ $emp->employee_id }}</td>
      <td style="border:1px solid #ddd;padding:5px 8px;font-weight:600">{{ $emp->name }}</td>
      <td style="border:1px solid #ddd;padding:5px 8px;font-size:10.5px">{{ $emp->branch?->name ?? '—' }}</td>
      <td style="border:1px solid #ddd;padding:5px 8px;text-align:center;font-weight:700;color:#ef4444">{{ $absents->count() }}</td>
      <td style="border:1px solid #ddd;padding:5px 8px;font-size:10px;color:#64748b">
        {{ $absents->pluck('date')->map(fn($d)=>$d->format('d-M'))->implode(', ') }}
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
