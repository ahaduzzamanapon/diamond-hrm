@extends('layouts.app')
@section('title','Shifts')
@section('breadcrumb')<span class="current">Shift Management</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Shift Management</h1><p class="page-subtitle">Configure work shifts and timings</p></div>
  <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')"><i class="bi bi-plus-lg"></i> Add Shift</button>
</div>

<div class="grid g-3 mb-20">
  @forelse($shifts as $shift)
  <div class="glass-card" style="padding:20px">
    <div class="flex-between mb-16">
      <div>
        <div style="font-size:15px;font-weight:700">{{ $shift->name }}</div>
        <div class="text-muted fs-13">{{ $shift->employees_count }} employee(s)</div>
      </div>
      <span class="badge {{ $shift->is_active ? 'badge-success' : 'badge-danger' }}">{{ $shift->is_active ? 'Active' : 'Inactive' }}</span>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
      <div style="background:#f8fafc;border-radius:8px;padding:10px;text-align:center">
        <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:3px">Start</div>
        <div style="font-size:18px;font-weight:700">{{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}</div>
      </div>
      <div style="background:#f8fafc;border-radius:8px;padding:10px;text-align:center">
        <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:3px">End</div>
        <div style="font-size:18px;font-weight:700">{{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}</div>
      </div>
    </div>
    <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:14px">
      @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i=>$day)
        @php $key = strtolower(['sunday','monday','tuesday','wednesday','thursday','friday','saturday'][$i]); @endphp
        <span style="padding:3px 8px;border-radius:20px;font-size:11px;font-weight:600;
          {{ $shift->$key ? 'background:#0a0a0a;color:#fff' : 'background:#f1f5f9;color:var(--text-muted)' }}">{{ $day }}</span>
      @endforeach
    </div>
    <div class="flex gap-8">
      <button class="btn btn-sm btn-secondary" style="flex:1" onclick="editShift({{ $shift->id }},'{{ addslashes($shift->name) }}','{{ $shift->start_time }}','{{ $shift->end_time }}',{{ $shift->grace_minutes }},{{ $shift->sunday?1:0 }},{{ $shift->monday?1:0 }},{{ $shift->tuesday?1:0 }},{{ $shift->wednesday?1:0 }},{{ $shift->thursday?1:0 }},{{ $shift->friday?1:0 }},{{ $shift->saturday?1:0 }})">
        <i class="bi bi-pencil"></i> Edit
      </button>
      <form method="POST" action="{{ route('shifts.destroy',$shift) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form>
    </div>
  </div>
  @empty
  <div class="glass-card" style="grid-column:span 3;padding:40px">
    <div class="empty-state"><div class="empty-icon">🕐</div><h3>No shifts yet</h3></div>
  </div>
  @endforelse
</div>

{{-- Add Modal --}}
<div class="modal-overlay" id="addModal">
  <div class="modal" style="max-width:560px">
    <div class="modal-header"><span class="modal-title">Add Shift</span><button class="modal-close" onclick="document.getElementById('addModal').classList.remove('open')">&times;</button></div>
    <form method="POST" action="{{ route('shifts.store') }}">@csrf
      <div class="modal-body">
        <div class="grid g-2 gap-12">
          <div class="form-group" style="grid-column:span 2"><label class="form-label">Shift Name <span class="req">*</span></label><input name="name" class="form-control" required placeholder="e.g. Morning Shift"></div>
          <div class="form-group"><label class="form-label">Start Time <span class="req">*</span></label><input type="time" name="start_time" class="form-control" required></div>
          <div class="form-group"><label class="form-label">End Time <span class="req">*</span></label><input type="time" name="end_time" class="form-control" required></div>
          <div class="form-group"><label class="form-label">Grace Minutes</label><input type="number" name="grace_minutes" class="form-control" value="10" min="0" max="60"></div>
          <div class="form-group"><label class="form-label">Break Minutes</label><input type="number" name="break_minutes" class="form-control" value="60" min="0"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Working Days</label>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px">
            @foreach(['sunday','monday','tuesday','wednesday','thursday','friday','saturday'] as $day)
              <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;padding:6px 10px;border:1.5px solid var(--clr-border);border-radius:8px">
                <input type="checkbox" name="{{ $day }}" value="1" style="accent-color:#0a0a0a"> {{ ucfirst($day) }}
              </label>
            @endforeach
          </div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button><button type="submit" class="btn btn-primary">Save Shift</button></div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal-overlay" id="editModal">
  <div class="modal" style="max-width:560px">
    <div class="modal-header"><span class="modal-title">Edit Shift</span><button class="modal-close" onclick="document.getElementById('editModal').classList.remove('open')">&times;</button></div>
    <form method="POST" id="editForm" action="">@csrf @method('PUT')
      <div class="modal-body">
        <div class="grid g-2 gap-12">
          <div class="form-group" style="grid-column:span 2"><label class="form-label">Shift Name</label><input name="name" id="eName" class="form-control" required></div>
          <div class="form-group"><label class="form-label">Start Time</label><input type="time" name="start_time" id="eStart" class="form-control" required></div>
          <div class="form-group"><label class="form-label">End Time</label><input type="time" name="end_time" id="eEnd" class="form-control" required></div>
          <div class="form-group"><label class="form-label">Grace Minutes</label><input type="number" name="grace_minutes" id="eGrace" class="form-control"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Working Days</label>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px">
            @foreach(['sunday','monday','tuesday','wednesday','thursday','friday','saturday'] as $day)
              <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;padding:6px 10px;border:1.5px solid var(--clr-border);border-radius:8px">
                <input type="checkbox" name="{{ $day }}" value="1" class="day-check" data-day="{{ $day }}" style="accent-color:#0a0a0a"> {{ ucfirst($day) }}
              </label>
            @endforeach
          </div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
    </form>
  </div>
</div>
@endsection
@push('scripts')
<script>
function editShift(id, name, start, end, grace, sun, mon, tue, wed, thu, fri, sat) {
  document.getElementById('editForm').action = '/shifts/' + id;
  document.getElementById('eName').value = name;
  document.getElementById('eStart').value = start;
  document.getElementById('eEnd').value = end;
  document.getElementById('eGrace').value = grace;
  const days = { sunday: sun, monday: mon, tuesday: tue, wednesday: wed, thursday: thu, friday: fri, saturday: sat };
  document.querySelectorAll('.day-check').forEach(cb => { cb.checked = !!days[cb.dataset.day]; });
  document.getElementById('editModal').classList.add('open');
}
</script>
@endpush
