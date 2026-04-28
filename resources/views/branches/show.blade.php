@extends('layouts.app')
@section('title', 'Branch — ' . $branch->name)
@section('breadcrumb')
  <a href="{{ route('branches.index') }}">Branches</a> &rsaquo; 
  <span class="current">{{ $branch->name }}</span>
@endsection

@section('content')
<div class="page-header">
  <div style="display:flex;align-items:center;gap:16px">
    @if($branch->logo)
      <img src="{{ asset('storage/'.$branch->logo) }}" style="width:56px;height:56px;border-radius:12px;object-fit:cover">
    @else
      <div style="width:56px;height:56px;border-radius:12px;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:24px">
        {{ strtoupper(substr($branch->name,0,1)) }}
      </div>
    @endif
    <div>
      <h1 class="page-title" style="margin:0">{{ $branch->name }}</h1>
      <p class="page-subtitle" style="margin:0">Branch Code: {{ $branch->code ?? '—' }}</p>
    </div>
  </div>
  <div class="flex gap-8">
    <a href="{{ route('branches.edit', $branch) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit</a>
    <a href="{{ route('branches.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
  </div>
</div>

<div style="margin-bottom:16px">
  <span class="badge {{ ($branch->is_active ?? true) ? 'badge-success' : 'badge-danger' }}" style="font-size:13px;padding:6px 14px">
    {{ ($branch->is_active ?? true) ? 'Active' : 'Inactive' }}
  </span>
</div>

<div class="grid g-3 gap-16 mb-20">
  <div class="stat-card">
    <div class="stat-icon">👥</div>
    <div class="stat-value">{{ $branch->employees()->count() ?? 0 }}</div>
    <div class="stat-label">Total Employees</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon">🏢</div>
    <div class="stat-value">{{ $branch->departments()->count() ?? 0 }}</div>
    <div class="stat-label">Departments</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
  <div class="glass-card mb-16">
    <div class="card-header"><div class="card-title"><i class="bi bi-info-circle"></i> Branch Details</div></div>
    <div class="card-body">
      <table style="width:100%;font-size:13px;line-height:1.9">
        <tr><td class="text-muted" style="width:30%">Name</td><td class="fw-600">{{ $branch->name }}</td></tr>
        <tr><td class="text-muted">Code</td><td>{{ $branch->code ?? '—' }}</td></tr>
        <tr><td class="text-muted">Phone</td><td>{{ $branch->phone ?? '—' }}</td></tr>
        <tr><td class="text-muted">Email</td><td>{{ $branch->email ?? '—' }}</td></tr>
        <tr><td class="text-muted">Address</td><td>{{ $branch->address ?? '—' }}</td></tr>
      </table>
    </div>
  </div>

  <div class="glass-card mb-16">
    <div class="card-header"><div class="card-title"><i class="bi bi-diagram-3"></i> Departments</div></div>
    <div class="card-body">
       <ul style="padding-left: 20px;">
           @forelse($branch->departments as $dept)
              <li class="mb-8" style="font-size:14px;"><strong>{{ $dept->name }}</strong></li>
           @empty
              <li class="text-muted fs-13">No departments created in this branch yet.</li>
           @endforelse
       </ul>
    </div>
  </div>
</div>

@endsection
