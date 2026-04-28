@extends('layouts.app')
@section('title','Add Employee')
@section('breadcrumb')<a href="{{ route('employees.index') }}">Employees</a> &rsaquo; <span class="current">Add</span>@endsection

@push('styles')
<style>
  .form-section { background:#f8fafc; border-radius:8px; padding:16px; margin-bottom:16px; border-left:3px solid var(--primary); }
  .form-section-title { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--primary); margin-bottom:12px; }
</style>
@endpush

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Add Employee</h1><p class="page-subtitle">Create a new employee record</p></div>
  <a href="{{ route('employees.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

@if($errors->any())
<div class="alert alert-danger mb-16"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
@csrf

{{-- ── Personal Info ────────────────────────────────────────────────────── --}}
<div class="glass-card mb-16">
  <div class="card-header"><div class="card-title"><i class="bi bi-person-badge"></i> Personal Information</div></div>
  <div class="card-body">
    <div class="grid g-3 gap-12" style="margin-bottom:12px">
      <div class="form-group">
        <label class="form-label">First Name <span class="req">*</span></label>
        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
      </div>
      <div class="form-group">
        <label class="form-label">Last Name</label>
        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
      </div>
      <div class="form-group">
        <label class="form-label">Employee ID <span class="text-muted fs-12">(auto if blank)</span></label>
        <input type="text" name="employee_id" class="form-control" value="{{ old('employee_id') }}" placeholder="EMP-0001">
      </div>
    </div>
    <div class="grid g-4 gap-12">
      <div class="form-group">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-control">
          <option value="male" {{ old('gender')=='male'?'selected':'' }}>Male</option>
          <option value="female" {{ old('gender')=='female'?'selected':'' }}>Female</option>
          <option value="other" {{ old('gender')=='other'?'selected':'' }}>Other</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
      </div>
      <div class="form-group">
        <label class="form-label">Blood Group</label>
        <select name="blood_group" class="form-control">
          <option value="">—</option>
          @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
            <option value="{{ $bg }}" {{ old('blood_group')==$bg?'selected':'' }}>{{ $bg }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">NID / Passport</label>
        <input type="text" name="nid" class="form-control" value="{{ old('nid') }}">
      </div>
    </div>
    <div class="grid g-2 gap-12 mt-12">
      <div class="form-group">
        <label class="form-label">Present Address</label>
        <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Permanent Address</label>
        <textarea name="permanent_address" class="form-control" rows="2">{{ old('permanent_address') }}</textarea>
      </div>
    </div>
  </div>
</div>

{{-- ── Contact & Login ──────────────────────────────────────────────────── --}}
<div class="glass-card mb-16">
  <div class="card-header"><div class="card-title"><i class="bi bi-telephone"></i> Contact & System Access</div></div>
  <div class="card-body">
    <div class="grid g-3 gap-12">
      <div class="form-group">
        <label class="form-label">Phone <span class="req">*</span></label>
        <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number') }}" required placeholder="01XXXXXXXXX">
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="For system login">
      </div>
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" value="{{ old('username') }}">
      </div>
      <div class="form-group">
        <label class="form-label">Password <span class="text-muted fs-12">(default: password)</span></label>
        <input type="password" name="password" class="form-control" placeholder="Leave blank = default">
      </div>
      <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control">
      </div>
      <div class="form-group">
        <label class="form-label">System Role <span class="req">*</span></label>
        <select name="role" class="form-control" required>
          @foreach($roles as $role)
            <option value="{{ $role->name }}" {{ old('role')==$role->name?'selected':'' }}>{{ ucfirst(str_replace('-',' ',$role->name)) }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>
</div>

{{-- ── Employment ───────────────────────────────────────────────────────── --}}
<div class="glass-card mb-16">
  <div class="card-header"><div class="card-title"><i class="bi bi-briefcase"></i> Employment Details</div></div>
  <div class="card-body">
    <div class="grid g-3 gap-12">
      <div class="form-group">
        <label class="form-label">Branch <span class="req">*</span></label>
        <select name="branch_id" class="form-control" required id="branchSel" onchange="loadDepts()">
          <option value="">Select Branch</option>
          @foreach($branches as $b)<option value="{{ $b->id }}" {{ old('branch_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Department <span class="req">*</span></label>
        <select name="department_id" class="form-control" required id="deptSel" onchange="loadDesigs()">
          <option value="">Select Department</option>
          @foreach($departments as $d)<option value="{{ $d->id }}" {{ old('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Designation <span class="req">*</span></label>
        <select name="designation_id" class="form-control" required id="desigSel">
          <option value="">Select Designation</option>
          @foreach($designations as $d)<option value="{{ $d->id }}" {{ old('designation_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Shift</label>
        <select name="shift_id" class="form-control">
          <option value="">No Shift</option>
          @foreach($shifts as $s)<option value="{{ $s->id }}" {{ old('shift_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Joining Date <span class="req">*</span></label>
        <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date') }}" required>
      </div>
      <div class="form-group">
        <label class="form-label">Probation (months)</label>
        <input type="number" name="probation_months" class="form-control" value="{{ old('probation_months',3) }}" min="0">
      </div>
      <div class="form-group">
        <label class="form-label">Employee Type</label>
        <select name="employee_or_lead" class="form-control">
          <option value="employee" {{ old('employee_or_lead')=='employee'?'selected':'' }}>Employee</option>
          <option value="lead" {{ old('employee_or_lead')=='lead'?'selected':'' }}>Team Lead</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Team Leader</label>
        <select name="team_leader_id" class="form-control">
          <option value="">None</option>
          @foreach($leads as $l)<option value="{{ $l->id }}" {{ old('team_leader_id')==$l->id?'selected':'' }}>{{ $l->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Leave Category</label>
        <select name="leave_category" class="form-control">
          <option value="">Default</option>
          @foreach($leaveTypes as $lt)<option value="{{ $lt->id }}" {{ old('leave_category')==$lt->id?'selected':'' }}>{{ $lt->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Status <span class="req">*</span></label>
        <select name="status" class="form-control" required>
          <option value="active" {{ old('status','active')=='active'?'selected':'' }}>Active</option>
          <option value="inactive" {{ old('status')=='inactive'?'selected':'' }}>Inactive</option>
          <option value="terminated" {{ old('status')=='terminated'?'selected':'' }}>Terminated</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Biometric Punch ID</label>
        <input type="text" name="punch_id" class="form-control" value="{{ old('punch_id') }}" placeholder="ZKTeco User ID">
      </div>
    </div>
  </div>
</div>

{{-- ── Salary ───────────────────────────────────────────────────────────── --}}
<div class="glass-card mb-16">
  <div class="card-header"><div class="card-title"><i class="bi bi-cash-stack"></i> Salary & Allowances</div></div>
  <div class="card-body">
    <div class="grid g-4 gap-12">
      <div class="form-group">
        <label class="form-label">Basic Salary</label>
        <input type="number" name="basic_salary" class="form-control" value="{{ old('basic_salary',0) }}" min="0" step="0.01">
      </div>
      <div class="form-group">
        <label class="form-label">House Rent</label>
        <input type="number" name="house_rent_allowance" class="form-control" value="{{ old('house_rent_allowance',0) }}" min="0" step="0.01">
      </div>
      <div class="form-group">
        <label class="form-label">Medical</label>
        <input type="number" name="medical_allowance" class="form-control" value="{{ old('medical_allowance',0) }}" min="0" step="0.01">
      </div>
      <div class="form-group">
        <label class="form-label">Transport</label>
        <input type="number" name="transport_allowance" class="form-control" value="{{ old('transport_allowance',0) }}" min="0" step="0.01">
      </div>
    </div>
    <div class="grid g-2 gap-12 mt-12">
      <div class="form-group">
        <label class="form-label">Bank Name</label>
        <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}" placeholder="e.g. BRAC Bank">
      </div>
      <div class="form-group">
        <label class="form-label">Bank Account No.</label>
        <input type="text" name="bank_account" class="form-control" value="{{ old('bank_account') }}">
      </div>
    </div>
  </div>
</div>

{{-- ── Documents & Emergency ────────────────────────────────────────────── --}}
<div class="glass-card mb-16">
  <div class="card-header"><div class="card-title"><i class="bi bi-file-person"></i> Documents & Emergency Contact</div></div>
  <div class="card-body">
    <div class="grid g-2 gap-12">
      <div class="form-group">
        <label class="form-label">Photo</label>
        <input type="file" name="photo" class="form-control" accept="image/*">
      </div>
      <div class="form-group">
        <label class="form-label">Note / Document</label>
        <input type="file" name="note_file" class="form-control">
      </div>
    </div>
    <div class="form-group mt-12">
      <label class="form-label">Remarks</label>
      <textarea name="remark" class="form-control" rows="2">{{ old('remark') }}</textarea>
    </div>
    <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--clr-border)">
      <div class="fs-13 fw-600 mb-12">Emergency Contact</div>
      <div class="grid g-4 gap-12">
        <div class="form-group">
          <label class="form-label">Name</label>
          <input type="text" name="ec_name" class="form-control" value="{{ old('ec_name') }}">
        </div>
        <div class="form-group">
          <label class="form-label">Relation</label>
          <input type="text" name="ec_relation" class="form-control" value="{{ old('ec_relation') }}" placeholder="Father, Spouse…">
        </div>
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="text" name="ec_phone" class="form-control" value="{{ old('ec_phone') }}">
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <input type="text" name="ec_address" class="form-control" value="{{ old('ec_address') }}">
        </div>
      </div>
    </div>
  </div>
</div>

<div style="display:flex;gap:8px;justify-content:flex-end;margin-bottom:32px">
  <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
  <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus"></i> Create Employee</button>
</div>
</form>
@endsection

@push('scripts')
<script>
function loadDepts() {
  const branchId = document.getElementById('branchSel').value;
  if (!branchId) return;
  fetch(`/employees/ajax/departments?branch_id=${branchId}`)
    .then(r=>r.json()).then(data=>{
      const sel = document.getElementById('deptSel');
      sel.innerHTML = '<option value="">Select Department</option>';
      data.forEach(d => sel.innerHTML += `<option value="${d.id}">${d.name}</option>`);
    });
}
function loadDesigs() {
  const deptId = document.getElementById('deptSel').value;
  if (!deptId) return;
  fetch(`/employees/ajax/designations?department_id=${deptId}`)
    .then(r=>r.json()).then(data=>{
      const sel = document.getElementById('desigSel');
      sel.innerHTML = '<option value="">Select Designation</option>';
      data.forEach(d => sel.innerHTML += `<option value="${d.id}">${d.name}</option>`);
    });
}
</script>
@endpush
