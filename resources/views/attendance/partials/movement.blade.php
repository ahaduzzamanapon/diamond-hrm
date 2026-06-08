@php
  $periodLabel = ($date1 === $date2) 
      ? \Carbon\Carbon::parse($date1)->format('d M Y') 
      : \Carbon\Carbon::parse($date1)->format('d M Y') . ' — ' . \Carbon\Carbon::parse($date2)->format('d M Y');
@endphp
<div style="margin-bottom:12px;font-family:'Times New Roman',serif;text-align:center">
  <div style="font-weight:700;font-size:15px;letter-spacing:1px">EMPLOYEE BRANCH MOVEMENT REPORT</div>
  <div style="font-size:12px;margin-top:4px">{{ $periodLabel }}</div>
</div>

<table style="width:100%;border-collapse:collapse;font-size:11.5px">
  <thead>
    <tr>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555;text-align:center">SL</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555;text-align:center">Emp ID</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555;text-align:left;min-width:130px">Name</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555;text-align:center">Date</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555;text-align:center">Day</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555;text-align:left">From Branch</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555;text-align:center">Out Time (Depart)</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555;text-align:left">To Branch</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555;text-align:center">In Time (Arrive)</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555;text-align:center">Travel Duration</th>
      <th style="background:#0a0a0a;color:#fff;padding:7px 9px;border:1px solid #555;text-align:left">Punches (Depart → Arrive)</th>
    </tr>
  </thead>
  <tbody>
    @forelse($movements as $i => $move)
    <tr style="background:{{ $i % 2 == 0 ? '#fff' : '#f9fafb' }}">
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center">{{ $i + 1 }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center;font-size:11px">{{ $move['employee']->employee_id }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;font-weight:600">{{ $move['employee']->name }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center">{{ $move['date']->format('d-M-y') }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center;font-size:11px">{{ $move['date']->format('D') }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px">{{ $move['from_branch'] }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center;font-weight:600;color:#ef4444">
        {{ $move['departure_time']->format('h:i A') }}
      </td>
      <td style="border:1px solid #ddd;padding:5px 9px">{{ $move['to_branch'] }}</td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center;font-weight:600;color:#10b981">
        {{ $move['arrival_time']->format('h:i A') }}
      </td>
      <td style="border:1px solid #ddd;padding:5px 9px;text-align:center;font-weight:700;color:#3b82f6">
        {{ $move['duration'] }}
      </td>
      <td style="border:1px solid #ddd;padding:5px 9px;font-size:11px;color:#64748b">
        <span style="background:#f1f5f9;color:#475569;padding:2px 6px;border-radius:4px;font-weight:600;text-transform:uppercase">
          {{ $move['depart_punch_type'] ?: 'unknown' }}
        </span>
        →
        <span style="background:#f1f5f9;color:#475569;padding:2px 6px;border-radius:4px;font-weight:600;text-transform:uppercase">
          {{ $move['arrive_punch_type'] ?: 'unknown' }}
        </span>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="11" style="text-align:center;padding:30px;color:#94a3b8;font-size:13px">
        No employee branch movement recorded for the selected criteria.
      </td>
    </tr>
    @endforelse
  </tbody>
  @if(count($movements))
  <tfoot>
    <tr style="background:#f1f5f9;font-weight:700">
      <td colspan="5" style="border:1px solid #ddd;padding:7px 9px">Total Movements: {{ count($movements) }}</td>
      <td colspan="6" style="border:1px solid #ddd;padding:7px 9px;text-align:right;color:#64748b;font-size:11px">
        * A movement represents consecutive biometric punches at different branch locations on the same day.
      </td>
    </tr>
  </tfoot>
  @endif
</table>
