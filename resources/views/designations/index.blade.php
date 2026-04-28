@extends('layouts.app')
@section('title','Designations')
@section('breadcrumb')<span class="current">Designations</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Designations</h1><p class="page-subtitle">Job titles and grades</p></div>
  <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')"><i class="bi bi-plus-lg"></i> Add Designation</button>
</div>

<div class="glass-card">
  <div class="table-wrapper">
    <table>
      <thead><tr><th>#</th><th>Designation</th><th>Grade</th><th>Department</th><th>Branch</th><th>Employees</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($designations as $des)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td><strong>{{ $des->name }}</strong></td>
          <td>{{ $des->grade ?? '—' }}</td>
          <td>{{ $des->department?->name ?? '—' }}</td>
          <td>{{ $des->department?->branch?->name ?? '—' }}</td>
          <td><span class="badge badge-primary">{{ $des->employees_count }}</span></td>
          <td><span class="badge {{ $des->is_active ? 'badge-success' : 'badge-danger' }}">{{ $des->is_active ? 'Active' : 'Inactive' }}</span></td>
          <td>
            <div class="flex gap-8">
              <button class="btn btn-sm btn-secondary" onclick="editDes({{ $des->id }},'{{ addslashes($des->name) }}','{{ $des->grade }}',{{ $des->department_id ?? 'null' }})"><i class="bi bi-pencil"></i></button>
              <form method="POST" action="{{ route('designations.destroy',$des) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">🏷️</div><h3>No designations yet</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="modal-overlay" id="addModal">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Add Designation</span><button class="modal-close" onclick="document.getElementById('addModal').classList.remove('open')">&times;</button></div>
    <form method="POST" action="{{ route('designations.store') }}">@csrf
      <div class="modal-body">
        <div class="grid g-2 gap-12">
          <div class="form-group"><label class="form-label">Name <span class="req">*</span></label><input name="name" class="form-control" required></div>
          <div class="form-group"><label class="form-label">Grade</label><input name="grade" class="form-control" placeholder="e.g. G1, M2"></div>
        </div>
        <div class="form-group"><label class="form-label">Department <span class="req">*</span></label>
          <select name="department_id" class="form-control" required>
            <option value="">Select Department</option>
            @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Edit Designation</span><button class="modal-close" onclick="document.getElementById('editModal').classList.remove('open')">&times;</button></div>
    <form method="POST" id="editForm" action="">@csrf @method('PUT')
      <div class="modal-body">
        <div class="grid g-2 gap-12">
          <div class="form-group"><label class="form-label">Name</label><input name="name" id="editName" class="form-control" required></div>
          <div class="form-group"><label class="form-label">Grade</label><input name="grade" id="editGrade" class="form-control"></div>
        </div>
        <div class="form-group"><label class="form-label">Department</label>
          <select name="department_id" id="editDept" class="form-control">
            @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
    </form>
  </div>
</div>
@endsection
@push('scripts')
<script>
function editDes(id, name, grade, deptId) {
  document.getElementById('editForm').action = '/designations/' + id;
  document.getElementById('editName').value = name;
  document.getElementById('editGrade').value = grade || '';
  if(deptId) document.getElementById('editDept').value = deptId;
  document.getElementById('editModal').classList.add('open');
}
</script>
@endpush
