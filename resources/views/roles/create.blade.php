@extends('layouts.app')
@section('title','Create Role')
@section('breadcrumb')<a href="{{ route('roles.index') }}">Role Management</a><span class="sep">/</span><span class="current">Add Role</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Create Access Role</h1><p class="page-subtitle">Add a custom administrative role to your organization</p></div>
</div>

<form method="POST" action="{{ route('roles.store') }}">
  @csrf
  <div class="glass-card mb-16">
    <div class="card-header"><div class="card-title"><i class="bi bi-shield-check"></i> Role Identity</div></div>
    <div class="card-body">
      <div class="form-group" style="max-width:400px">
        <label class="form-label">Role Title <span class="req">*</span></label>
        <input type="text" name="name" class="form-control" required placeholder="e.g. Account Auditor">
        @error('name')<div class="text-danger mt-4 fs-12">{{ $message }}</div>@enderror
      </div>
    </div>
  </div>

  <div class="glass-card mb-16">
    <div class="card-header"><div class="card-title"><i class="bi bi-ui-checks-grid"></i> Access Permissions</div></div>
    <div class="card-body">
      @error('permissions')<div class="alert alert-danger mb-16"><i class="bi bi-exclamation-octagon"></i> {{ $message }}</div>@enderror
      
      <div class="grid g-3 gap-16">
        @foreach($permissions as $group => $perms)
          <div style="background:#f8fafc;padding:16px;border-radius:10px;border:1px solid var(--clr-border)">
            <h4 style="font-size:12px;text-transform:uppercase;margin-bottom:12px;color:var(--text-secondary);letter-spacing:1px;font-weight:700">
              Module: <span style="color:var(--clr-primary)">{{ ucfirst($group) }}</span>
            </h4>
            <div style="display:flex;flex-direction:column;gap:10px">
              @foreach($perms as $perm)
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                  <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" style="width:16px;height:16px;cursor:pointer">
                  <span class="fs-13 fw-500">{{ ucwords(str_replace('_', ' ', $perm->name)) }}</span>
                </label>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="flex" style="justify-content:flex-end">
    <a href="{{ route('roles.index') }}" class="btn btn-secondary mr-8">Cancel</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Role</button>
  </div>
</form>
@endsection
