@extends('layouts.app')
@section('title','Biometric Mapping')
@section('breadcrumb')<a href="{{ route('biometric.devices') }}">Biometric</a> &rsaquo; <span class="current">Employee Mapping</span>@endsection

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Employee Mapping</h1>
    <p class="page-subtitle">Link employee records to their biometric device user IDs</p>
  </div>
  <a href="{{ route('biometric.devices') }}" class="btn btn-secondary"><i class="bi bi-fingerprint"></i> Devices</a>
</div>

@if(session('success'))
  <div class="alert alert-success mb-16"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

<div class="glass-card">
  <div class="card-header">
    <div class="card-title"><i class="bi bi-diagram-3"></i> Map Employees to Biometric IDs</div>
    <span class="badge badge-info fs-12">{{ $employees->count() }} employees</span>
  </div>
  <form method="POST" action="{{ route('biometric.mapping.update') }}">
    @csrf
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Employee</th>
            <th>Branch</th>
            <th>Designation</th>
            <th style="width:200px">Biometric User ID <span class="text-muted fs-12">(from device)</span></th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($employees as $emp)
          <tr>
            <td>
              <div class="flex gap-8" style="align-items:center">
                <img src="{{ $emp->photo_url }}" class="avatar avatar-sm">
                <div>
                  <div class="fw-600 fs-13">{{ $emp->name }}</div>
                  <div class="text-muted" style="font-size:11px">{{ $emp->employee_id }}</div>
                </div>
              </div>
            </td>
            <td class="fs-13">{{ $emp->branch?->name ?? '—' }}</td>
            <td class="fs-13">{{ $emp->designation?->name ?? '—' }}</td>
            <td>
              <input type="text"
                     name="mappings[{{ $emp->id }}]"
                     value="{{ $emp->biometric_user_id }}"
                     class="form-control"
                     style="max-width:160px"
                     placeholder="e.g. 1, 2, 10032">
            </td>
            <td>
              @if($emp->biometric_user_id)
                <span class="badge badge-success"><i class="bi bi-link-45deg"></i> Mapped</span>
              @else
                <span class="badge badge-secondary">Not Mapped</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div style="padding:16px 18px;border-top:1px solid var(--clr-border);display:flex;gap:8px;justify-content:flex-end">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Mapping</button>
    </div>
  </form>
</div>

<div class="glass-card mt-16">
  <div class="card-header">
    <div class="card-title"><i class="bi bi-info-circle"></i> How to find Biometric User IDs</div>
  </div>
  <div class="card-body">
    <ol style="padding-left:20px;font-size:13px;color:var(--text-secondary);line-height:1.8">
      <li>Go to your <strong>ZKTeco device menu</strong> → User Management → Browse Users</li>
      <li>Each user has a <strong>User ID</strong> (numeric) — enter that number here</li>
      <li>Alternatively, look in the <a href="{{ route('biometric.logs') }}" class="text-primary">Punch Logs</a> → "Unmapped" rows show the raw UID from the device</li>
      <li>After mapping, run <a href="{{ route('biometric.sync') }}" class="text-primary">Sync</a> to process any pending logs</li>
    </ol>
  </div>
</div>
@endsection
