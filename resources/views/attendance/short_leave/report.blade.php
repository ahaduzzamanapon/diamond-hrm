@extends('layouts.app')
@section('title','Short Leave Report')
@section('breadcrumb')<a href="{{ route('attendance.index') }}">Attendance</a><span class="sep">/</span><a href="{{ route('attendance.short-leave.index') }}">Short Leave</a><span class="sep">/</span><span class="current">Report</span>@endsection

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Short Leave Report</h1>
    <p class="page-subtitle">Filter করে কর্মচারীর সাময়িক অনুপস্থিতির রিপোর্ট দেখুন</p>
  </div>
  <div class="flex gap-8">
    @if($rows->isNotEmpty())
    <button onclick="printReport()" class="btn btn-secondary"><i class="bi bi-printer"></i> Print / PDF</button>
    @endif
    <a href="{{ route('attendance.short-leave.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Entry</a>
  </div>
</div>

{{-- Full-screen filter + results layout --}}
<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;min-width:0">

  {{-- Filter Panel --}}
  <div class="glass-card" style="position:sticky;top:80px">
    <div class="card-header"><div class="card-title"><i class="bi bi-funnel-fill"></i> Filters</div></div>
    <div class="card-body">
      <form method="GET" action="{{ route('attendance.short-leave.report') }}">
        <div class="form-group">
          <label class="form-label">Employee</label>
          <select name="employee_id" class="form-control">
            <option value="">All Employees</option>
            @foreach($employees as $emp)
              <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }} ({{ $emp->employee_id }})</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Branch</label>
          <select name="branch_id" class="form-control">
            <option value="">All Branches</option>
            @foreach($branches as $b)
              <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Department</label>
          <select name="dept_id" class="form-control">
            <option value="">All Departments</option>
            @foreach($departments as $d)
              <option value="{{ $d->id }}" {{ request('dept_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">From Date</label>
          <input type="date" name="date_from" class="form-control" value="{{ request('date_from', date('Y-m-01')) }}">
        </div>
        <div class="form-group">
          <label class="form-label">To Date</label>
          <input type="date" name="date_to" class="form-control" value="{{ request('date_to', date('Y-m-d')) }}">
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option value="">All</option>
            <option value="pending"  {{ request('status') == 'pending'  ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary w-full mt-8"><i class="bi bi-search"></i> Generate Report</button>
        <a href="{{ route('attendance.short-leave.report') }}" class="btn btn-secondary w-full mt-8">Reset</a>
      </form>
    </div>
  </div>

  {{-- Report Area (Full-width) --}}
  <div id="reportArea" style="min-width:0;overflow:auto">
    @if($rows->isEmpty())
      <div class="glass-card">
        <div class="card-body">
          <div class="empty-state">
            <div class="empty-icon">🕐</div>
            <h3>No data yet</h3>
            <p>Select filters and click Generate Report</p>
          </div>
        </div>
      </div>
    @else
    <div class="glass-card" id="printable">
      <div class="card-header" style="justify-content:space-between;flex-wrap:wrap;gap:10px">
        <div class="card-title"><i class="bi bi-file-earmark-text"></i> Short Leave Report</div>
        <div style="font-size:12px;color:var(--text-muted)">
          Total: <strong>{{ $rows->count() }}</strong> records ·
          Total Duration: <strong>
            @php $totalMin = $rows->sum('duration_minutes'); $h = intdiv($totalMin,60); $m = $totalMin%60; @endphp
            {{ $h > 0 ? "{$h}h {$m}m" : "{$m}m" }}
          </strong>
        </div>
      </div>
      <div class="card-body" style="padding:0;overflow-x:auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Employee</th>
              <th>Dept / Branch</th>
              <th>Out Time</th>
              <th>Return Time</th>
              <th>Duration</th>
              <th>Reason</th>
              <th>Remarks</th>
              <th>Status</th>
              <th>Approved By</th>
            </tr>
          </thead>
          <tbody>
            @foreach($rows as $i => $sl)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td style="white-space:nowrap">{{ $sl->date->format('d M Y') }}</td>
              <td>
                <div style="font-weight:600">{{ $sl->employee->name }}</div>
                <div style="font-size:11px;color:var(--text-muted)">{{ $sl->employee->employee_id }}</div>
              </td>
              <td style="font-size:12px">
                {{ $sl->employee->department?->name ?? '—' }}<br>
                <span style="color:var(--text-muted)">{{ $sl->employee->branch?->name ?? '—' }}</span>
              </td>
              <td style="font-weight:600">{{ $sl->out_time }}</td>
              <td>{{ $sl->in_time ?? '—' }}</td>
              <td>
                @if($sl->duration_minutes > 0)
                  <span style="font-weight:700;color:#b45309">{{ $sl->duration_formatted }}</span>
                @else
                  <span style="color:var(--text-muted)">—</span>
                @endif
              </td>
              <td style="max-width:200px">{{ $sl->reason }}</td>
              <td style="max-width:200px">{{ $sl->remarks ?? '—' }}</td>
              <td>{!! $sl->status_badge !!}</td>
              <td style="font-size:12px">{{ $sl->approvedBy?->name ?? '—' }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="background:#f8fafc;font-weight:700">
              <td colspan="6" style="text-align:right;padding:10px 14px">Total Duration:</td>
              <td style="color:#b45309;font-size:14px">
                {{ $h > 0 ? "{$h}h {$m}m" : "{$m}m" }}
              </td>
              <td colspan="4"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    @endif
  </div>
</div>

<style>
.w-full { width:100%; }
@media print {
  .topbar, .sidebar, .page-header, aside, header, .sidebar-overlay,
  form, .btn, a[href] { display:none !important; }
  #reportArea { grid-column:1/-1; }
  body { background:#fff; }
}
</style>
@endsection

@push('scripts')
<script>
function printReport() {
  const content = document.getElementById('printable').innerHTML;
  const win = window.open('', '_blank');
  win.document.write(`<!DOCTYPE html><html><head>
    <title>Short Leave Report</title>
    <style>
      body { font-family: 'Inter', Arial, sans-serif; margin: 20px; color: #000; }
      table { width: 100%; border-collapse: collapse; font-size: 11px; }
      th, td { border: 1px solid #999; padding: 5px 7px; }
      thead th { background: #1a1a1a; color: #fff; }
      tfoot td { background: #f5f5f5; font-weight: bold; }
      .badge { padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; }
      @page { margin: 15mm; }
    </style>
  </head><body>${content}</body></html>`);
  win.document.close();
  setTimeout(() => { win.print(); }, 500);
}
</script>
@endpush
