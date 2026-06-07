@extends('layouts.app')
@section('title','Short Leave')
@section('breadcrumb')<a href="{{ route('attendance.index') }}">Attendance</a><span class="sep">/</span><span class="current">Short Leave</span>@endsection

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Short Leave Records</h1>
    <p class="page-subtitle">দিনের মধ্যে কর্মচারীর সাময়িক অনুপস্থিতির তালিকা</p>
  </div>
  <div class="flex gap-8">
    <a href="{{ route('attendance.short-leave.report') }}" class="btn btn-secondary"><i class="bi bi-bar-chart-line"></i> Report</a>
    @can('manage_attendance')
    <a href="{{ route('attendance.short-leave.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Entry</a>
    @endcan
  </div>
</div>

{{-- Filter bar --}}
<div class="glass-card mb-16">
  <div class="card-body">
    <form method="GET" action="{{ route('attendance.short-leave.index') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
      <div class="form-group" style="margin:0;flex:1;min-width:160px">
        <label class="form-label">Employee</label>
        <select name="employee_id" class="form-control">
          <option value="">All Employees</option>
          @foreach($employees as $emp)
            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group" style="margin:0;flex:1;min-width:140px">
        <label class="form-label">Branch</label>
        <select name="branch_id" class="form-control">
          <option value="">All Branches</option>
          @foreach($branches as $b)
            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group" style="margin:0;min-width:140px">
        <label class="form-label">From Date</label>
        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
      </div>
      <div class="form-group" style="margin:0;min-width:140px">
        <label class="form-label">To Date</label>
        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
      </div>
      <div class="form-group" style="margin:0;min-width:130px">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
          <option value="">All</option>
          <option value="pending"  {{ request('status') == 'pending'  ? 'selected' : '' }}>Pending</option>
          <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
          <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary" style="height:38px"><i class="bi bi-search"></i> Filter</button>
      <a href="{{ route('attendance.short-leave.index') }}" class="btn btn-secondary" style="height:38px">Reset</a>
    </form>
  </div>
</div>

{{-- Table --}}
<div class="glass-card">
  <div class="card-body" style="padding:0">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Employee</th>
          <th>Branch</th>
          <th>Date</th>
          <th>Out Time</th>
          <th>In Time</th>
          <th>Duration</th>
          <th>Reason</th>
          <th>Status</th>
          <th>Entered By</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($shortLeaves as $sl)
        <tr>
          <td>{{ $shortLeaves->firstItem() + $loop->index }}</td>
          <td>
            <div style="font-weight:600">{{ $sl->employee->name }}</div>
            <div style="font-size:11px;color:var(--text-muted)">{{ $sl->employee->employee_id }}</div>
          </td>
          <td>{{ $sl->employee->branch?->name ?? '—' }}</td>
          <td>{{ $sl->date->format('d M Y') }}</td>
          <td><span style="font-weight:600;color:#0f172a">{{ $sl->out_time }}</span></td>
          <td>{{ $sl->in_time ?? '<span style="color:var(--text-muted)">—</span>' }}</td>
          <td>
            @if($sl->duration_minutes > 0)
              <span style="font-weight:600;color:#b45309">{{ $sl->duration_formatted }}</span>
            @else
              <span style="color:var(--text-muted)">—</span>
            @endif
          </td>
          <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $sl->reason }}">{{ $sl->reason }}</td>
          <td>{!! $sl->status_badge !!}</td>
          <td style="font-size:12px">{{ $sl->enteredBy?->name ?? '—' }}</td>
          <td>
            @if($sl->status === 'pending')
              @can('manage_attendance')
              <form method="POST" action="{{ route('attendance.short-leave.approve', $sl) }}" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check-lg"></i></button>
              </form>
              <form method="POST" action="{{ route('attendance.short-leave.reject', $sl) }}" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger" title="Reject"><i class="bi bi-x-lg"></i></button>
              </form>
              @endcan
            @else
              <span style="font-size:11px;color:var(--text-muted)">{{ $sl->approvedBy?->name ?? '—' }}</span>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="11"><div class="empty-state"><div class="empty-icon">🕐</div><h3>No short leave records found</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($shortLeaves->hasPages())
  <div class="card-body" style="border-top:1px solid var(--clr-border);padding:12px 16px">
    {{ $shortLeaves->links() }}
  </div>
  @endif
</div>
@endsection
