@extends('layouts.app')
@section('title', 'Branch Transfer — ' . $employee->name)
@section('breadcrumb')
  <a href="{{ route('employees.index') }}">Employees</a>
  <span class="sep">/</span>
  <a href="{{ route('employees.show', $employee) }}">{{ $employee->name }}</a>
  <span class="sep">/</span>
  <span class="current">Branch Transfer</span>
@endsection

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Branch Transfer</h1>
    <p class="page-subtitle">Transfer {{ $employee->name }} to a new branch / shift</p>
  </div>
  <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary">
    <i class="bi bi-arrow-left"></i> Back to Profile
  </a>
</div>

@if(session('success'))
  <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

<div class="grid" style="grid-template-columns:1fr 1.6fr;gap:20px;align-items:start">

  {{-- ── Transfer Form ─────────────────────────────────── --}}
  <div class="glass-card" style="padding:24px">
    <h3 style="font-size:15px;font-weight:700;margin-bottom:18px">
      <i class="bi bi-arrow-left-right" style="color:var(--primary)"></i> New Transfer
    </h3>

    {{-- Current Info --}}
    <div style="background:var(--surface-2);border-radius:10px;padding:14px;margin-bottom:18px;font-size:13px">
      <div style="font-weight:600;margin-bottom:8px;color:var(--text-muted)">Current Assignment</div>
      <div class="flex gap-8" style="flex-wrap:wrap">
        <span class="badge badge-info">{{ $employee->branch?->name ?? '—' }}</span>
        <span class="badge badge-secondary">{{ $employee->department?->name ?? '—' }}</span>
        <span class="badge badge-warning">{{ $employee->shift?->name ?? 'No Shift' }}</span>
      </div>
    </div>

    <form method="POST" action="{{ route('employees.transfers.store', $employee) }}">
      @csrf

      <div class="form-group">
        <label class="form-label">To Branch <span style="color:red">*</span></label>
        <select name="to_branch_id" id="to_branch_id" class="form-control" required onchange="loadDepts(this.value)">
          <option value="">— Select Branch —</option>
          @foreach($branches as $b)
            <option value="{{ $b->id }}" {{ $employee->branch_id == $b->id ? 'disabled' : '' }}>
              {{ $b->name }} {{ $employee->branch_id == $b->id ? '(Current)' : '' }}
            </option>
          @endforeach
        </select>
        @error('to_branch_id')<div class="text-danger fs-13">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">To Department</label>
        <select name="to_department_id" id="to_department_id" class="form-control">
          <option value="">— Same as current —</option>
          @foreach($departments as $d)
            <option value="{{ $d->id }}">{{ $d->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">To Shift</label>
        <select name="to_shift_id" class="form-control">
          <option value="">— Same as current —</option>
          @foreach($shifts as $s)
            <option value="{{ $s->id }}" {{ $employee->shift_id == $s->id ? 'selected' : '' }}>
              {{ $s->name }} ({{ \Carbon\Carbon::parse($s->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($s->end_time)->format('h:i A') }})
            </option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Effective Date <span style="color:red">*</span></label>
        <input type="date" name="effective_date" class="form-control" value="{{ date('Y-m-d') }}" required>
        @error('effective_date')<div class="text-danger fs-13">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Reason / Remarks</label>
        <textarea name="reason" class="form-control" rows="3" placeholder="Optional transfer reason..."></textarea>
      </div>

      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-arrow-left-right"></i> Confirm Transfer
      </button>
    </form>
  </div>

  {{-- ── Transfer History ─────────────────────────────── --}}
  <div class="glass-card">
    <div style="padding:16px 18px;border-bottom:1px solid var(--border);font-weight:700;font-size:14px">
      <i class="bi bi-clock-history" style="color:var(--primary)"></i> Transfer History
      <span class="badge badge-info" style="margin-left:6px">{{ $transfers->count() }}</span>
    </div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>From Branch</th>
            <th>To Branch</th>
            <th>Shift Change</th>
            <th>Reason</th>
            <th>By</th>
          </tr>
        </thead>
        <tbody>
          @forelse($transfers as $t)
          <tr>
            <td class="fw-600 fs-13">{{ $t->effective_date->format('d M Y') }}</td>
            <td>
              <div class="fs-13">{{ $t->fromBranch?->name ?? '—' }}</div>
              <div class="text-muted" style="font-size:11px">{{ $t->fromDept?->name ?? '' }}</div>
            </td>
            <td>
              <div class="fs-13 fw-600" style="color:var(--primary)">{{ $t->toBranch?->name ?? '—' }}</div>
              <div class="text-muted" style="font-size:11px">{{ $t->toDept?->name ?? '' }}</div>
            </td>
            <td>
              @if($t->from_shift_id !== $t->to_shift_id)
                <div class="fs-13" style="color:#ef4444">{{ $t->fromShift?->name ?? '—' }}</div>
                <div style="font-size:10px;color:#94a3b8">↓</div>
                <div class="fs-13 fw-600" style="color:#10b981">{{ $t->toShift?->name ?? '—' }}</div>
              @else
                <span class="text-muted fs-13">No change</span>
              @endif
            </td>
            <td class="fs-13 text-muted">{{ $t->reason ?? '—' }}</td>
            <td class="fs-13">{{ $t->transferredBy?->name ?? '—' }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="6">
              <div class="empty-state">
                <div class="empty-icon">🏢</div>
                <h3>No transfers yet</h3>
                <p>This employee has not been transferred.</p>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
function loadDepts(branchId) {
  if (!branchId) return;
  fetch(`/employees/ajax/departments?branch_id=${branchId}`)
    .then(r => r.json())
    .then(data => {
      const sel = document.getElementById('to_department_id');
      sel.innerHTML = '<option value="">— Same as current —</option>';
      data.forEach(d => sel.innerHTML += `<option value="${d.id}">${d.name}</option>`);
    });
}
</script>
@endpush
