@extends('layouts.app')
@section('title','Leave Management')
@section('breadcrumb')<span class="current">Leave Management</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Leave Management</h1><p class="page-subtitle">Applications, approvals and balance tracking</p></div>
  @if(auth()->user()->hasRole(['Employee']))
  <button class="btn btn-primary" onclick="document.getElementById('applyModal').classList.add('open')">
    <i class="bi bi-plus-lg"></i> Apply for Leave
  </button>
  @endif
</div>

{{-- Tabs --}}
<div class="tabs mb-20">
  <button class="tab {{ request('tab','pending')=='pending' ? 'active' : '' }}" onclick="location.href='?tab=pending'">
    Pending <span class="badge badge-warning" style="margin-left:4px">{{ $pendingCount }}</span>
  </button>
  <button class="tab {{ request('tab')=='approved' ? 'active' : '' }}" onclick="location.href='?tab=approved'">Approved</button>
  <button class="tab {{ request('tab')=='rejected' ? 'active' : '' }}" onclick="location.href='?tab=rejected'">Rejected</button>
  <button class="tab {{ request('tab')=='all' ? 'active' : '' }}" onclick="location.href='?tab=all'">All</button>
</div>

<div class="glass-card">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr><th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th><th>Applied</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($leaves as $leave)
        <tr>
          <td>
            <div class="flex gap-8" style="align-items:center">
              <img src="{{ $leave->employee->photo_url }}" class="avatar avatar-sm">
              <div>
                <div class="fw-600 fs-13">{{ $leave->employee->name }}</div>
                <div class="text-muted" style="font-size:11px">{{ $leave->employee->branch?->name }}</div>
              </div>
            </div>
          </td>
          <td><span class="badge badge-info">{{ $leave->leaveType?->name ?? '—' }}</span></td>
          <td class="fs-13">{{ $leave->from_date->format('d M Y') }}</td>
          <td class="fs-13">{{ $leave->to_date->format('d M Y') }}</td>
          <td><span class="badge badge-secondary">{{ $leave->days }} day(s)</span></td>
          <td class="text-muted fs-13">{{ Str::limit($leave->reason, 40) }}</td>
          <td>
            <span class="badge {{ match($leave->status){ 'pending'=>'badge-warning','approved'=>'badge-success','rejected'=>'badge-danger',default=>'badge-secondary'} }}">
              {{ ucfirst($leave->status) }}
            </span>
          </td>
          <td class="text-muted fs-13">{{ $leave->created_at->format('d M') }}</td>
          <td>
            @if($leave->status === 'pending')
            <div class="flex gap-8">
              <form method="POST" action="{{ route('leaves.approve', $leave) }}">@csrf
                <button type="submit" class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check-lg"></i></button>
              </form>
              <form method="POST" action="{{ route('leaves.reject', $leave) }}">@csrf
                <button type="submit" class="btn btn-sm btn-danger" title="Reject"><i class="bi bi-x-lg"></i></button>
              </form>
            </div>
            @else
            <span class="text-muted fs-13">—</span>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">🌴</div><h3>No leave applications</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:14px 18px">{{ $leaves->withQueryString()->links() }}</div>
</div>

{{-- Apply Modal --}}
<div class="modal-overlay" id="applyModal">
  <div class="modal">
    <div class="modal-header"><span class="modal-title"><i class="bi bi-calendar-plus"></i> Apply for Leave</span><button class="modal-close" onclick="document.getElementById('applyModal').classList.remove('open')">&times;</button></div>
    <form method="POST" action="{{ route('leaves.apply.store') }}">@csrf
      <div class="modal-body">
        <div class="form-group"><label class="form-label">Leave Type <span class="req">*</span></label>
          <select name="leave_type_id" class="form-control" required>
            <option value="">Select Type</option>
            @foreach($leaveTypes as $lt)<option value="{{ $lt->id }}">{{ $lt->name }} ({{ $lt->days_per_year }} days/yr)</option>@endforeach
          </select>
        </div>
        <div class="grid g-2 gap-12">
          <div class="form-group"><label class="form-label">From <span class="req">*</span></label><input type="date" name="from_date" class="form-control" required></div>
          <div class="form-group"><label class="form-label">To <span class="req">*</span></label><input type="date" name="to_date" class="form-control" required></div>
        </div>
        <div class="form-group"><label class="form-label">Reason</label><textarea name="reason" class="form-control" rows="3" placeholder="Reason for leave..."></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('applyModal').classList.remove('open')">Cancel</button><button type="submit" class="btn btn-primary">Submit Application</button></div>
    </form>
  </div>
</div>
@endsection
