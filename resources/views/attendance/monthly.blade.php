@extends('layouts.app')
@section('title','Attendance Summary Report')
@section('breadcrumb')<a href="{{ route('attendance.index') }}">Attendance</a><span class="sep">/</span><span class="current">Summary Report</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Attendance Summary Statement</h1><p class="page-subtitle">Monthly summary for {{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}</p></div>
  <div class="flex gap-8">
    <form method="GET" class="flex gap-8">
      <input type="month" name="month" value="{{ $month }}" class="form-control" style="width:auto">
      <select name="branch_id" class="form-control" style="width:auto">
        <option value="">All Branches</option>
        @foreach($branches as $b)<option value="{{ $b->id }}" {{ request('branch_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
      </select>
      <button type="submit" class="btn btn-primary">View</button>
    </form>
    <button onclick="printReport()" class="btn btn-secondary"><i class="bi bi-printer"></i> Print</button>
  </div>
</div>

<div id="printArea">
<div style="text-align:center;margin-bottom:8px;font-family:'Times New Roman',serif">
  <div style="font-size:16px;font-weight:700;letter-spacing:1px">ATTENDANTS SUMMARY STATEMENT</div>
  <div style="font-size:12px;margin-top:4px">For the Date of {{ \Carbon\Carbon::parse($month.'-01')->format('01-M-y') }} To {{ \Carbon\Carbon::parse($month.'-01')->endOfMonth()->format('d-M-y') }}</div>
  <div style="font-size:12px">Branch ID: {{ request('branch_id') ? $branches->find(request('branch_id'))?->code : 'ALL' }} &nbsp;|&nbsp; {{ request('branch_id') ? $branches->find(request('branch_id'))?->name : 'All Branches' }}</div>
</div>

<div class="glass-card" style="overflow-x:auto;padding:0">
  <table class="report-table">
    <thead>
      <tr>
        <th rowspan="2" style="min-width:30px">SL</th>
        <th rowspan="2" style="min-width:50px">Emp ID</th>
        <th rowspan="2" style="min-width:140px">Employee Name</th>
        <th rowspan="2">Duty Day</th>
        <th rowspan="2">Present</th>
        <th rowspan="2">Absent</th>
        <th colspan="2">Weekend</th>
        <th colspan="3">Leave</th>
        <th rowspan="2">Late</th>
        <th rowspan="2">Lat Abst</th>
        <th rowspan="2">Ov. Late</th>
        <th rowspan="2">Pnd.Lev</th>
        <th rowspan="2">P.Ab Day</th>
        <th rowspan="2">Net Paid</th>
        <th rowspan="2" style="min-width:80px">Remarks</th>
        <th rowspan="2" style="min-width:60px">App.By M.D.</th>
      </tr>
      <tr>
        <th>W.E.</th><th>W.W.</th>
        <th>AP Leav</th><th>FLeave</th><th>Sick</th>
      </tr>
    </thead>
    <tbody>
      @forelse($employees as $i => $emp)
        @php
          $empAtt   = $allAtt->get($emp->id, collect());
          $dutyDays = $daysInMonth - $totalHolidays;
          $present  = $empAtt->whereIn('status',['present','late','half_day','extra_present'])->count();
          $absent   = $empAtt->where('status','absent')->count();
          $we       = 0; // weekends
          $ww       = $empAtt->where('status','extra_present')->count(); // weekend work
          $apLeave  = $empAtt->where('status','leave')->count();
          $fLeave   = 0;
          $sickLeave= 0;
          $late     = $empAtt->where('status','late')->count();
          $latAbst  = $empAtt->where('status','absent')->where('late_minutes','>',0)->count();
          $ovLate   = $empAtt->where('late_minutes','>',60)->count();
          $pndLev   = \App\Models\LeaveApplication::where('employee_id',$emp->id)->where('status','pending')->count();
          $pAbDay   = 0;
          $netPaid  = max(0, $present - $pAbDay);
          // Count weekends
          for($d=1;$d<=$daysInMonth;$d++){
            $day=\Carbon\Carbon::createFromDate($year,$mon,$d);
            if($day->isWeekend()) $we++;
          }
        @endphp
        <tr class="{{ $i%2==0?'':'tr-alt' }}">
          <td>{{ $i+1 }}</td>
          <td style="font-size:11px">{{ $emp->employee_id }}</td>
          <td>{{ $emp->name }}</td>
          <td class="text-center">{{ $dutyDays }}</td>
          <td class="text-center fw-600 text-success">{{ $present }}</td>
          <td class="text-center fw-600 text-danger">{{ $absent }}</td>
          <td class="text-center">{{ $we }}</td>
          <td class="text-center">{{ $ww }}</td>
          <td class="text-center">{{ $apLeave }}</td>
          <td class="text-center">{{ $fLeave }}</td>
          <td class="text-center">{{ $sickLeave }}</td>
          <td class="text-center text-warning">{{ $late }}</td>
          <td class="text-center">{{ $latAbst }}</td>
          <td class="text-center">{{ $ovLate }}</td>
          <td class="text-center">{{ $pndLev }}</td>
          <td class="text-center">{{ $pAbDay }}</td>
          <td class="text-center fw-600">{{ $netPaid }}</td>
          <td></td>
          <td></td>
        </tr>
      @empty
        <tr><td colspan="20" class="text-center" style="padding:30px;color:#999">No employees found</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div style="margin-top:16px;font-size:11px;color:#666;padding:0 4px">
  <strong>Legend:</strong> W.E. = Week End, W.W. = Weekend Work, AP Leav = Annual/Privileged Leave, FLeave = Festival Leave, Lat Abst = Late Absent, Ov. Late = Over Late, Pnd.Lev = Pending Leave, P.Ab Day = Paid Absent Day
</div>
</div>

<style>
.report-table { width:100%; border-collapse:collapse; font-size:11.5px; font-family:'Inter',sans-serif; }
.report-table th, .report-table td { border:1px solid #ccc; padding:5px 6px; white-space:nowrap; }
.report-table thead th { background:#0a0a0a; color:#fff; text-align:center; font-size:10.5px; }
.report-table tbody tr:hover td { background:#f0f7ff; }
.tr-alt td { background:#fafafa; }
.text-center { text-align:center; }
.text-success { color:#10b981; }
.text-danger  { color:#ef4444; }
.text-warning { color:#f59e0b; }
.fw-600 { font-weight:600; }
@media print {
  .topbar, .sidebar, .page-header, .sidebar-overlay { display:none !important; }
  .main-content { margin:0 !important; padding:0 !important; }
  .glass-card { box-shadow:none !important; border:none !important; }
  #printArea { padding:10px; }
  body { background:#fff; }
}
</style>
@endsection
@push('scripts')
<script>
function printReport() { window.print(); }
</script>
@endpush
