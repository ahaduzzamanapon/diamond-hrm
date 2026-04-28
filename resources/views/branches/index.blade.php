@extends('layouts.app')
@section('title','Branches')
@section('breadcrumb')<span class="current">Branches</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Branches</h1><p class="page-subtitle">Manage company branch locations</p></div>
  <a href="{{ route('branches.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Branch</a>
</div>

@if(session('success'))
  <div class="alert alert-success mb-16"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

<div class="glass-card">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr><th>#</th><th>Branch Name</th><th>Code</th><th>Location</th><th>Phone</th><th>Employees</th><th>Departments</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($branches as $branch)
        <tr>
          <td class="fs-12 text-muted">{{ $loop->iteration }}</td>
          <td>
            <div class="flex gap-8" style="align-items:center">
              @if($branch->logo)
                <img src="{{ asset('storage/'.$branch->logo) }}" style="width:32px;height:32px;border-radius:6px;object-fit:cover">
              @else
                <div style="width:32px;height:32px;border-radius:6px;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px">
                  {{ strtoupper(substr($branch->name,0,1)) }}
                </div>
              @endif
              <div class="fw-600 fs-13">{{ $branch->name }}</div>
            </div>
          </td>
          <td><code class="fs-12">{{ $branch->code ?? '—' }}</code></td>
          <td class="fs-13 text-muted">{{ $branch->address ?? '—' }}</td>
          <td class="fs-13">{{ $branch->phone ?? '—' }}</td>
          <td><span class="badge badge-info">{{ $branch->employees_count ?? 0 }}</span></td>
          <td><span class="badge badge-secondary">{{ $branch->departments_count ?? 0 }}</span></td>
          <td>
            <span class="badge {{ ($branch->is_active ?? true) ? 'badge-success' : 'badge-danger' }}">
              {{ ($branch->is_active ?? true) ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td>
            <div class="flex gap-8">
              <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
              <form method="POST" action="{{ route('branches.destroy', $branch) }}" onsubmit="return confirm('Delete {{ $branch->name }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">🏢</div><h3>No branches yet</h3><p><a href="{{ route('branches.create') }}" class="text-primary">Add your first branch</a></p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:14px 18px">{{ $branches->links() }}</div>
</div>
@endsection
