@extends('layouts.app')
@section('title','Attendance Register')
@section('breadcrumb')<a href="{{ route('attendance.index') }}">Attendance</a><span class="sep">/</span><span class="current">Register Statement</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Attendance Register Statement</h1><p class="page-subtitle">Individual daily attendance with punch times</p></div>
  <div class="flex gap-8">
    <form method="GET" class="flex gap-8">
      <select name="employee_id" class="form-control" style="min-width:200px" required>
        <option value="">Select Employee...</option>
        @foreach($employees as $e)
          <option value="{{ $e->id }}" {{ request('employee_id')==$e->id?'selected':'' }}>{{ $e->name }} ({{ $e->employee_id }})</option>
        @endforeach
      </select>
      <input type="month" name="month" value="{{ $month }}" class="form-control" style="width:auto">
      <button type="submit" class="btn btn-primary">View</button>
    </form>
    @if($employee)
    <button onclick="printReport()" class="btn btn-secondary"><i class="bi bi-printer"></i> Print</button>
    @endif
  </div>
</div>

@if($employee)
<div id="printArea">
<div style="text-align:center;margin-bottom:12px;font-family:'Times New Roman',serif">
  <div style="font-size:16px;font-weight:700;letter-spacing:1px">ATTENDANTS REGISTER STATEMENT</div>
  <div style="margin-top:12px;font-size:13px;font-weight:700">{{ $employee->name }}</div>
  <div style="font-size:12px">{{ $employee->employee_id }}</div>
</div>

<div class="glass-card" style="overflow-x:auto;padding:0">
  <table class="report-table">
    <thead>
      <tr>
        <th rowspan="2">Date</th>
        <th rowspan="2">Day</th>
        <th colspan="3" style="background:#1a1a1a">Entrance Time</th>
        <th colspan="3" style="background:#333">Exit Time</th>
        <th rowspan="2">Door / Device</th>
        <th rowspan="2">Att. Status</th>
        <th rowspan="2" style="min-width:80px">Remarks</th>
      </tr>
      <tr>
        <th>Off. Time</th><th>Actual Time</th><th>Diff.</th>
        <th>Off. Time</th><th>Actual Time</th><th>Diff.</th>
      </tr>
    </thead>
    <tbody>
      @foreach($days as $day)
        @php
          $att     = $dayMap->get($day->format('Y-m-d'));
          $isWeekend = $day->isWeekend();
          $holiday   = $holidays->get($day->format('Y-m-d'));
          $isHoliday = !is_null($holiday);
          $shift     = $employee->shift;

          $offIn  = $shift ? \Carbon\Carbon::parse($day->format('Y-m-d').' '.$shift->start_time)->format('h:i:s A') : '—';
          $offOut = $shift ? \Carbon\Carbon::parse($day->format('Y-m-d').' '.$shift->end_time)->format('h:i:s A') : '—';

          $actIn  = $att?->in_time  ? \Carbon\Carbon::parse($att->in_time)->format('h:i:s A')  : null;
          $actOut = $att?->out_time ? \Carbon\Carbon::parse($att->out_time)->format('h:i:s A') : null;

          $diffIn = null;
          if ($shift && $actIn) {
            $mins = \Carbon\Carbon::parse($day->format('Y-m-d').' '.$shift->start_time)->diffInSeconds(\Carbon\Carbon::parse($att->in_time), false);
            $late = $mins > 0;
            $diffIn = ($late ? '+' : '-') . gmdate('H:i:s', abs($mins));
          }
          $diffOut = null;
          if ($shift && $actOut) {
            $secs = \Carbon\Carbon::parse($day->format('Y-m-d').' '.$shift->end_time)->diffInSeconds(\Carbon\Carbon::parse($att->out_time), false);
            $early = $secs > 0;
            $diffOut = ($early ? '+' : '-') . gmdate('H:i:s', abs($secs));
          }

          $status = $att?->status ?? ($isHoliday ? 'Holiday' : ($isWeekend ? 'Weekend' : ''));
          $rowStyle = '';
          if ($isWeekend || $isHoliday) $rowStyle = 'background:#fff9f0';
          if ($status === 'absent') $rowStyle = 'background:#fff5f5';
        @endphp
        <tr style="{{ $rowStyle }}">
          <td style="white-space:nowrap">{{ $day->format('d-M-y') }}</td>
          <td>{{ strtoupper($day->format('l')) }}</td>
          <td class="text-center">{{ $offIn }}</td>
          <td class="text-center {{ ($att?->late_minutes??0)>0?'text-danger':'' }}">{{ $actIn ?? '' }}</td>
          <td class="text-center text-danger" style="font-size:10.5px">{{ $diffIn }}</td>
          <td class="text-center">{{ $offOut }}</td>
          <td class="text-center">{{ $actOut ?? '' }}</td>
          <td class="text-center text-danger" style="font-size:10.5px">{{ $diffOut }}</td>
          <td class="text-center fs-12">
            {{ $att?->device?->name ?? ($att ? 'Manual' : '') }}
          </td>
          <td class="text-center">
            @if($status)
              <span style="font-weight:600;color:{{ match(strtolower($status)){'present'=>'#10b981','absent'=>'#ef4444','late'=>'#f59e0b','leave'=>'#3b82f6','holiday'=>'#8b5cf6','weekend'=>'#94a3b8',default=>'#0a0a0a'} }}">
                {{ ucfirst($status) }}
              </span>
            @endif
          </td>
          <td>
            @if($isHoliday) <span style="font-size:10px;color:#8b5cf6">{{ $holiday }}</span>@endif
            {{ $att?->note }}
          </td>
        </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr style="background:#f8fafc;font-weight:700">
        <td colspan="2">TOTAL</td>
        <td colspan="3" class="text-center">Present: {{ $summary['present'] }}</td>
        <td colspan="3" class="text-center">Absent: {{ $summary['absent'] }}</td>
        <td class="text-center">Late: {{ $summary['late'] }}</td>
        <td class="text-center">Leave: {{ $summary['leave'] }}</td>
        <td></td>
      </tr>
    </tfoot>
  </table>
</div>
</div>

<style>
.report-table { width:100%; border-collapse:collapse; font-size:11.5px; font-family:'Inter',sans-serif; }
.report-table th, .report-table td { border:1px solid #ccc; padding:5px 8px; white-space:nowrap; }
.report-table thead th { background:#0a0a0a; color:#fff; text-align:center; font-size:10.5px; }
.report-table tbody tr:hover td { background:#f0f7ff !important; }
.text-center { text-align:center; }
.text-danger  { color:#dc2626; }
.fs-12 { font-size:12px; }
@media print {
  .topbar, .sidebar, .page-header, .sidebar-overlay { display:none !important; }
  .main-content { margin:0 !important; padding:0 !important; }
  .glass-card { box-shadow:none !important; }
  #printArea { padding:10px; }
  body { background:#fff; }
  .report-table thead th { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
}
</style>
@else
<div class="glass-card">
  <div class="empty-state" style="padding:60px">
    <div class="empty-icon">📊</div>
    <h3>Select an employee to view their attendance register</h3>
    <p class="text-muted fs-13 mt-8">Choose an employee and month from the filters above.</p>
  </div>
</div>
@endif
@endsection
@push('scripts')
<script>function printReport(){ window.print(); }</script>
@endpush
