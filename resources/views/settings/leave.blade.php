@extends('layouts.app')
@section('title','Leave Settings')
@section('breadcrumb')<span class="current">Settings — Leave</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Leave Settings</h1><p class="page-subtitle">Configure leave types and carry-forward rules</p></div>
</div>

{{-- Sub-nav --}}
<div class="flex gap-8 mb-20">
  <a href="{{ route('settings.general') }}" class="btn btn-secondary">General</a>
  <a href="{{ route('settings.leave') }}" class="btn btn-primary">Leave</a>
</div>

@if(session('success'))
  <div class="alert alert-success mb-16"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

{{-- Leave Policies --}}
<form method="POST" action="{{ route('settings.leave.update') }}" class="mb-20">
  @csrf
  <div class="glass-card">
    <div class="card-header"><div class="card-title"><i class="bi bi-sliders"></i> Leave Policies</div></div>
    <div class="card-body">
      <div class="grid g-2 gap-16">
        <div style="display:flex;align-items:center;gap:12px;background:#f8fafc;padding:16px;border-radius:8px">
          <input type="checkbox" name="carry_forward_leave" value="1" id="carry_fwd"
            {{ ($settings['carry_forward_leave'] ?? '0') === '1' ? 'checked' : '' }}
            style="width:20px;height:20px;cursor:pointer">
          <div>
            <label for="carry_fwd" class="fw-600 fs-14" style="cursor:pointer">Enable Leave Carry Forward</label>
            <div class="text-muted fs-12">Unused leave days carry over to the next year</div>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px;background:#f8fafc;padding:16px;border-radius:8px">
          <input type="checkbox" name="leave_auto_carry" value="1" id="auto_carry"
            {{ ($settings['leave_auto_carry'] ?? '0') === '1' ? 'checked' : '' }}
            style="width:20px;height:20px;cursor:pointer">
          <div>
            <label for="auto_carry" class="fw-600 fs-14" style="cursor:pointer">Automatic Carry Forward</label>
            <div class="text-muted fs-12">Auto-carry at year end without manual action</div>
          </div>
        </div>
      </div>
    </div>
    <div style="padding:14px 20px;border-top:1px solid var(--clr-border);display:flex;justify-content:flex-end">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Policy</button>
    </div>
  </div>
</form>

{{-- Leave Types --}}
<div class="glass-card">
  <div class="card-header">
    <div class="card-title"><i class="bi bi-tags"></i> Leave Types</div>
    <button class="btn btn-sm btn-primary" onclick="document.getElementById('addLeaveTypeModal').classList.add('open')">
      <i class="bi bi-plus-lg"></i> Add Type
    </button>
  </div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr><th>Name</th><th>Code</th><th>Days / Year</th><th>Carry Forward</th><th>Paid</th><th>Status</th></tr>
      </thead>
      <tbody>
        @forelse($leaveTypes as $lt)
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:8px">
              @if($lt->color)
                <span style="width:10px;height:10px;border-radius:50%;background:{{ $lt->color }};display:inline-block"></span>
              @endif
              <span class="fw-600 fs-13">{{ $lt->name }}</span>
            </div>
          </td>
          <td class="fs-13"><code>{{ $lt->code ?? '—' }}</code></td>
          <td><span class="badge badge-info">{{ $lt->days_per_year }} days</span></td>
          <td>
            @if($lt->carry_forward)
              <span class="badge badge-success">Yes</span>
            @else
              <span class="badge badge-secondary">No</span>
            @endif
          </td>
          <td>
            @if($lt->is_paid)
              <span class="badge badge-success">Paid</span>
            @else
              <span class="badge badge-secondary">Unpaid</span>
            @endif
          </td>
          <td>
            <span class="badge {{ $lt->is_active ? 'badge-success' : 'badge-danger' }}">
              {{ $lt->is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
        </tr>
        @empty
        <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">📋</div><h3>No leave types added yet</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Add Leave Type Modal --}}
<div class="modal-overlay" id="addLeaveTypeModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Add Leave Type</span>
      <button class="modal-close" onclick="document.getElementById('addLeaveTypeModal').classList.remove('open')">&times;</button>
    </div>
    <form method="POST" action="{{ route('settings.leave-type.store') }}">
      @csrf
      <div class="modal-body">
        <div class="grid g-2 gap-12">
          <div class="form-group">
            <label class="form-label">Name <span class="req">*</span></label>
            <input type="text" name="name" class="form-control" required placeholder="e.g. Casual Leave">
          </div>
          <div class="form-group">
            <label class="form-label">Code</label>
            <input type="text" name="code" class="form-control" placeholder="e.g. CL">
          </div>
          <div class="form-group">
            <label class="form-label">Days Per Year <span class="req">*</span></label>
            <input type="number" name="days_per_year" class="form-control" required min="1" placeholder="10">
          </div>
          <div class="form-group">
            <label class="form-label">Color</label>
            <input type="color" name="color" class="form-control" value="#6366f1" style="height:40px;padding:4px">
          </div>
        </div>
        <div class="grid g-3 gap-12 mt-12">
          <div style="display:flex;align-items:center;gap:8px">
            <input type="checkbox" name="carry_forward" value="1" id="cf" style="width:16px;height:16px">
            <label for="cf" class="fs-13">Carry Forward</label>
          </div>
          <div style="display:flex;align-items:center;gap:8px">
            <input type="checkbox" name="is_paid" value="1" id="ip" checked style="width:16px;height:16px">
            <label for="ip" class="fs-13">Paid Leave</label>
          </div>
          <div style="display:flex;align-items:center;gap:8px">
            <input type="checkbox" name="is_active" value="1" id="ia" checked style="width:16px;height:16px">
            <label for="ia" class="fs-13">Active</label>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('addLeaveTypeModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Leave Type</button>
      </div>
    </form>
  </div>
</div>
@endsection
