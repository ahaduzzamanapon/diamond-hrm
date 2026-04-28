@extends('layouts.app')
@section('title','Attendance')
@section('breadcrumb')<span class="current">Attendance</span>@endsection

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Attendance</h1>
    <p class="page-subtitle">Daily attendance records — {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</p>
  </div>
  <div class="flex gap-8">
    <a href="{{ route('attendance.monthly') }}" class="btn btn-secondary"><i class="bi bi-calendar3"></i> Monthly View</a>
    <a href="{{ route('attendance.manual') }}" class="btn btn-primary"><i class="bi bi-pencil-square"></i> Manual Entry</a>
  </div>
</div>

{{-- Filter --}}
<div class="filter-bar">
  <form method="GET" class="flex gap-8 flex-wrap" style="width:100%">
    <div class="form-group"><label class="form-label">Date</label><input type="date" name="date" class="form-control" value="{{ $date }}"></div>
    <div class="form-group"><label class="form-label">Branch</label>
      <select name="branch_id" class="form-control">
        <option value="">All Branches</option>
        @foreach($branches as $b)<option value="{{ $b->id }}" {{ request('branch_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
      </select>
    </div>
    <div class="form-group"><label class="form-label">Status</label>
      <select name="status" class="form-control">
        <option value="">All Status</option>
        @foreach(['present','absent','late','half_day','leave','holiday','weekend'] as $s)
          <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-group"><label class="form-label">&nbsp;</label><button type="submit" class="btn btn-primary">Filter</button></div>
  </form>
</div>

{{-- Summary Badges --}}
<div class="flex gap-8 mb-16 flex-wrap">
  @foreach(['present'=>['success','✅'],'absent'=>['danger','❌'],'late'=>['warning','⏰'],'half_day'=>['info','½'],'leave'=>['purple','🌴']] as $st=>[$color,$icon])
    <span class="badge badge-{{ $color }}" style="padding:6px 12px;font-size:12px">
      {{ $icon }} {{ ucfirst(str_replace('_',' ',$st)) }}: <strong>{{ $summary[$st] ?? 0 }}</strong>
    </span>
  @endforeach
</div>

<div class="glass-card">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr><th>Employee</th><th>Branch</th><th>Dept.</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Note</th><th>Source</th><th>Entered By</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($attendances as $att)
        <tr>
          <td>
            <div class="flex gap-8" style="align-items:center">
              <img src="{{ $att->employee->photo_url }}" class="avatar avatar-sm">
              <div>
                <div class="fw-600 fs-13">{{ $att->employee->name }}</div>
                <div class="text-muted" style="font-size:11px">{{ $att->employee->employee_id }}</div>
              </div>
            </div>
          </td>
          <td class="fs-13">{{ $att->employee->branch?->name ?? '—' }}</td>
          <td class="fs-13">{{ $att->employee->department?->name ?? '—' }}</td>
          <td class="fs-13">{{ $att->in_time  ? \Carbon\Carbon::parse($att->in_time)->format('h:i A')  : '—' }}</td>
          <td class="fs-13">{{ $att->out_time ? \Carbon\Carbon::parse($att->out_time)->format('h:i A') : '—' }}</td>
          <td>
            <span class="badge att-{{ $att->status }}">{{ strtoupper(substr($att->status,0,1)) }}</span>
            <span class="fs-13">{{ ucfirst(str_replace('_',' ',$att->status)) }}</span>
          </td>
          <td class="text-muted fs-13">{{ Str::limit($att->note,30) }}</td>
          <td class="fs-13">
            @if($att->source === 'biometric')
              <span class="badge badge-info" title="Biometric"><i class="bi bi-fingerprint"></i></span>
            @elseif($att->source === 'import')
              <span class="badge badge-warning" title="Import"><i class="bi bi-upload"></i></span>
            @else
              <span class="badge badge-secondary" title="Manual"><i class="bi bi-pencil-square"></i></span>
            @endif
          </td>
          <td class="fs-13 text-muted">
            {{ $att->enteredBy?->name ?? ($att->source === 'biometric' ? 'Biometric' : '—') }}
          </td>
          <td>
            @can('manage_attendance')
            <button class="btn btn-sm btn-secondary" onclick="editAtt({{ $att->id }},'{{ $att->status }}','{{ $att->in_time }}','{{ $att->out_time }}','{{ addslashes($att->note) }}')">
              <i class="bi bi-pencil"></i>
            </button>
            @endcan
          </td>
        </tr>
        @empty
        <tr><td colspan="10"><div class="empty-state"><div class="empty-icon">📋</div><h3>No records for this date</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:14px 18px">{{ $attendances->withQueryString()->links() }}</div>
</div>

{{-- Edit Modal --}}
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Edit Attendance</span><button class="modal-close" onclick="document.getElementById('editModal').classList.remove('open')">&times;</button></div>
    <form method="POST" id="editForm">@csrf @method('PUT')
      <div class="modal-body">
        <div class="form-group"><label class="form-label">Status</label>
          <select name="status" id="editStatus" class="form-control">
            @foreach(['present','absent','late','half_day','leave'] as $s)<option value="{{ $s }}">{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach
          </select>
        </div>
        <div class="grid g-2 gap-12">
          <div class="form-group"><label class="form-label">Check In</label><input type="time" name="check_in" id="editIn" class="form-control"></div>
          <div class="form-group"><label class="form-label">Check Out</label><input type="time" name="check_out" id="editOut" class="form-control"></div>
        </div>
        <div class="form-group"><label class="form-label">Note</label><textarea name="note" id="editNote" class="form-control" rows="2"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
    </form>
  </div>
</div>
@endsection
@push('scripts')
<script>
function editAtt(id, status, checkIn, checkOut, note) {
  document.getElementById('editForm').action = '/attendance/' + id;
  document.getElementById('editStatus').value = status;
  document.getElementById('editIn').value = checkIn ? checkIn.substr(11,5) : '';
  document.getElementById('editOut').value = checkOut ? checkOut.substr(11,5) : '';
  document.getElementById('editNote').value = note;
  document.getElementById('editModal').classList.add('open');
}
</script>
@endpush
