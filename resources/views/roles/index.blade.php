@extends('layouts.app')
@section('title','Role Management')
@section('breadcrumb')<span class="current">Settings — Role Management</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Role Management</h1><p class="page-subtitle">Configure system roles and permission mapping</p></div>
  <a href="{{ route('roles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add New Role</a>
</div>

@if(session('success'))
  <div class="alert alert-success mt-12 mb-16"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert alert-danger mt-12 mb-16"><i class="bi bi-exclamation-octagon-fill"></i> {{ session('error') }}</div>
@endif

<div class="glass-card">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th style="min-width:180px">Role Name</th>
          <th>Total Permissions</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($roles as $role)
          <tr>
            <td>
              <span class="badge badge-primary fw-600 fs-13" style="text-transform:uppercase; letter-spacing:1px; background:rgba(var(--clr-primary-rgb),0.1); color:var(--clr-primary)">
                {{ str_replace('-', ' ', $role->name) }}
              </span>
            </td>
            <td>
              @if($role->name === 'super-admin')
                <span class="badge badge-success">Full System Access (All)</span>
              @else
                <span class="fs-13 fw-600 text-muted">{{ $role->permissions->count() }} active permissions</span>
              @endif
            </td>
            <td>
              @if($role->name === 'super-admin')
                 <span class="fs-12 text-muted fw-600"><i class="bi bi-lock-fill"></i> System Locked</span>
              @else
                <div class="flex gap-8">
                  <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-secondary"><i class="bi bi-pencil-square"></i> Modify</a>
                  @if(!in_array($role->name, ['hr-admin', 'branch-manager', 'hr', 'staff']))
                  <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this custom role?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm" style="background:var(--clr-danger);color:#fff"><i class="bi bi-trash"></i></button>
                  </form>
                  @endif
                </div>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3">
              <div class="empty-state">
                <div class="empty-icon">🛡️</div>
                <h3>No roles found</h3>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
