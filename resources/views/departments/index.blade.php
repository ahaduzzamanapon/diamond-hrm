@extends('layouts.app')
@section('title','Departments')
@section('breadcrumb')<span class="current">Departments</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Departments</h1><p class="page-subtitle">Manage departments across branches</p></div>
  <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')"><i class="bi bi-plus-lg"></i> Add Department</button>
</div>

<div class="glass-card">
  <div class="table-wrapper">
    <table>
      <thead><tr><th>#</th><th>Department</th><th>Code</th><th>Branch</th><th>Employees</th><th>Designations</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($departments as $dept)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td><strong>{{ $dept->name }}</strong></td>
          <td><span class="badge badge-secondary">{{ $dept->code ?? '—' }}</span></td>
          <td>{{ $dept->branch?->name ?? '—' }}</td>
          <td><span class="badge badge-primary">{{ $dept->employees_count }}</span></td>
          <td><span class="badge badge-info">{{ $dept->designations_count }}</span></td>
          <td><span class="badge {{ $dept->is_active ? 'badge-success' : 'badge-danger' }}">{{ $dept->is_active ? 'Active' : 'Inactive' }}</span></td>
          <td>
            <div class="flex gap-8">
              <button class="btn btn-sm btn-secondary" onclick="editDept({{ $dept->id }},'{{ addslashes($dept->name) }}','{{ $dept->code }}',{{ $dept->branch_id ?? 'null' }})"><i class="bi bi-pencil"></i></button>
              <form method="POST" action="{{ route('departments.destroy',$dept) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">🗂️</div><h3>No departments yet</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:14px 18px">{{ $departments->links() }}</div>
</div>

{{-- Add Modal --}}
<div class="modal-overlay" id="addModal">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Add Department</span><button class="modal-close" onclick="document.getElementById('addModal').classList.remove('open')">&times;</button></div>
    <form method="POST" action="{{ route('departments.store') }}">@csrf
      <div class="modal-body">
        <div class="grid g-2 gap-12">
          <div class="form-group"><label class="form-label">Name <span class="req">*</span></label><input name="name" class="form-control" required></div>
          <div class="form-group"><label class="form-label">Code</label><input name="code" class="form-control"></div>
        </div>
        <div class="form-group"><label class="form-label">Branch <span class="req">*</span></label>
          <select name="branch_id" class="form-control" required>
            <option value="">Select Branch</option>
            @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group"><label class="form-label">Status</label><select name="is_active" class="form-control"><option value="1">Active</option><option value="0">Inactive</option></select></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Edit Department</span><button class="modal-close" onclick="document.getElementById('editModal').classList.remove('open')">&times;</button></div>
    <form method="POST" id="editForm" action="">@csrf @method('PUT')
      <div class="modal-body">
        <div class="grid g-2 gap-12">
          <div class="form-group"><label class="form-label">Name <span class="req">*</span></label><input name="name" id="editName" class="form-control" required></div>
          <div class="form-group"><label class="form-label">Code</label><input name="code" id="editCode" class="form-control"></div>
        </div>
        <div class="form-group"><label class="form-label">Branch</label>
          <select name="branch_id" id="editBranch" class="form-control">
            @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
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
function editDept(id, name, code, branchId) {
  document.getElementById('editForm').action = '/departments/' + id;
  document.getElementById('editName').value = name;
  document.getElementById('editCode').value = code || '';
  if(branchId) document.getElementById('editBranch').value = branchId;
  document.getElementById('editModal').classList.add('open');
}
</script>
@endpush
