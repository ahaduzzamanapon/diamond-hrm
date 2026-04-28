@extends('layouts.app')
@section('title','Employees')
@section('breadcrumb')<a href="{{ route('employees.index') }}">Employees</a><span class="sep">/</span><span class="current">All Employees</span>@endsection

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Employees</h1>
    <p class="page-subtitle">Manage all employee records across branches</p>
  </div>
  <div class="flex gap-8">
    <a href="{{ route('employees.export') }}" class="btn btn-secondary"><i class="bi bi-file-earmark-excel"></i> Export</a>
    <a href="{{ route('employees.import.form') }}" class="btn btn-secondary"><i class="bi bi-upload"></i> Import</a>
    <a href="{{ route('employees.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Employee</a>
  </div>
</div>

{{-- Filter Bar --}}
<div class="filter-bar">
  <form method="GET" class="flex gap-8 flex-wrap" style="width:100%">
    <div class="form-group" style="flex:2;min-width:200px">
      <label class="form-label">Search</label>
      <input type="text" name="search" class="form-control" placeholder="Name, ID or email…" value="{{ request('search') }}">
    </div>
    <div class="form-group" style="flex:1;min-width:150px">
      <label class="form-label">Branch</label>
      <select name="branch_id" class="form-control">
        <option value="">All Branches</option>
        @foreach($branches as $b)<option value="{{ $b->id }}" {{ request('branch_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
      </select>
    </div>
    <div class="form-group" style="flex:1;min-width:150px">
      <label class="form-label">Department</label>
      <select name="department_id" class="form-control">
        <option value="">All Depts</option>
        @foreach($departments as $d)<option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach
      </select>
    </div>
    <div class="form-group" style="min-width:130px">
      <label class="form-label">Status</label>
      <select name="status" class="form-control">
        <option value="">All</option>
        <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
        <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
        <option value="terminated" {{ request('status')=='terminated'?'selected':'' }}>Terminated</option>
      </select>
    </div>
    <div class="form-group" style="display:flex;align-items:flex-end;gap:8px;min-width:auto">
      <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
      <a href="{{ route('employees.index') }}" class="btn btn-secondary"><i class="bi bi-x-lg"></i></a>
    </div>
  </form>
</div>

{{-- Table --}}
<div class="glass-card">
  <div class="card-header">
    <div class="card-title"><i class="bi bi-people-fill" style="color:var(--accent)"></i> {{ $employees->total() }} Employees</div>
    <div class="flex gap-8">
      <span class="badge badge-success">Active: {{ $employees->where('status','active')->count() }}</span>
    </div>
  </div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Employee</th>
          <th>ID</th>
          <th>Branch</th>
          <th>Department</th>
          <th>Designation</th>
          <th>Shift</th>
          <th>Joining Date</th>
          <th>Status</th>
          <th style="width:100px">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($employees as $emp)
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <img src="{{ $emp->photo_url }}" class="avatar" alt="{{ $emp->name }}">
                <div>
                  <div style="font-weight:600;font-size:13.5px">{{ $emp->name }}</div>
                  <div style="font-size:11px;color:var(--text-muted)">{{ $emp->email }}</div>
                </div>
              </div>
            </td>
            <td><span class="badge badge-secondary" style="font-family:monospace">{{ $emp->employee_id }}</span></td>
            <td>{{ $emp->branch?->name ?? '—' }}</td>
            <td>{{ $emp->department?->name ?? '—' }}</td>
            <td>{{ $emp->designation?->name ?? '—' }}</td>
            <td>{{ $emp->shift?->name ?? '—' }}</td>
            <td>{{ $emp->joining_date?->format('d M Y') ?? '—' }}</td>
            <td>
              <span class="badge {{ match($emp->status) { 'active'=>'badge-success','inactive'=>'badge-warning','terminated'=>'badge-danger', default=>'badge-secondary' } }}">
                {{ ucfirst($emp->status) }}
              </span>
            </td>
            <td>
              <div class="flex gap-8">
                <a href="{{ route('employees.show',$emp) }}" class="btn btn-sm btn-secondary" title="View"><i class="bi bi-eye"></i></a>
                <a href="{{ route('employees.edit',$emp) }}" class="btn btn-sm btn-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('employees.destroy',$emp) }}" onsubmit="return confirm('Remove {{ $emp->name }}?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger" title="Remove"><i class="bi bi-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="9">
            <div class="empty-state">
              <div class="empty-icon">👤</div>
              <h3>No employees found</h3>
              <p>Try adjusting your filters or <a href="{{ route('employees.create') }}" style="color:var(--accent)">add an employee</a>.</p>
            </div>
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:16px 20px">
    {{ $employees->links() }}
  </div>
</div>
@endsection
