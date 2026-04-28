@extends('layouts.app')
@section('title','Biometric Punch Logs')
@section('breadcrumb')<a href="{{ route('biometric.devices') }}">Biometric</a> &rsaquo; <span class="current">Punch Logs</span>@endsection

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Punch Logs</h1>
    <p class="page-subtitle">Raw biometric punch records from all devices</p>
  </div>
  <div class="flex gap-8">
    <a href="{{ route('biometric.devices') }}" class="btn btn-secondary"><i class="bi bi-fingerprint"></i> Devices</a>
    <a href="{{ route('biometric.sync') }}" class="btn btn-primary" onclick="return confirm('Sync unprocessed logs into attendance records?')">
      <i class="bi bi-arrow-repeat"></i> Sync Now
    </a>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success mb-16"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif
@if(session('info'))
  <div class="alert alert-info mb-16"><i class="bi bi-info-circle-fill"></i> {{ session('info') }}</div>
@endif

{{-- Filter --}}
<div class="filter-bar">
  <form method="GET" class="flex gap-8 flex-wrap" style="width:100%">
    <div class="form-group">
      <label class="form-label">Month</label>
      <input type="month" name="month" class="form-control" value="{{ $month }}">
    </div>
    <div class="form-group">
      <label class="form-label">Employee</label>
      <select name="employee_id" class="form-control" style="min-width:200px">
        <option value="">All Employees</option>
        @foreach($employees as $e)
          <option value="{{ $e->id }}" {{ request('employee_id')==$e->id?'selected':'' }}>{{ $e->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-group"><label class="form-label">&nbsp;</label><button type="submit" class="btn btn-primary">Filter</button></div>
  </form>
</div>

{{-- Stats --}}
@php
  $total      = $logs->total();
  $processed  = $logs->getCollection()->where('processed',true)->count();
  $unprocessed= $logs->getCollection()->where('processed',false)->count();
@endphp
<div class="flex gap-8 mb-16 flex-wrap">
  <span class="badge badge-secondary" style="padding:6px 12px;font-size:12px">Total (page): <strong>{{ $logs->count() }}</strong></span>
  <span class="badge badge-success"   style="padding:6px 12px;font-size:12px">✅ Processed: <strong>{{ $processed }}</strong></span>
  <span class="badge badge-warning"   style="padding:6px 12px;font-size:12px">⏳ Unprocessed: <strong>{{ $unprocessed }}</strong></span>
</div>

<div class="glass-card">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Employee</th>
          <th>Device Serial</th>
          <th>Punch Time</th>
          <th>Type</th>
          <th>Verify</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
        <tr>
          <td class="fs-13 text-muted">{{ $log->id }}</td>
          <td>
            @if($log->employee)
              <div class="flex gap-8" style="align-items:center">
                <img src="{{ $log->employee->photo_url }}" class="avatar avatar-sm">
                <div>
                  <div class="fw-600 fs-13">{{ $log->employee->name }}</div>
                  <div class="text-muted" style="font-size:11px">{{ $log->employee->employee_id }}</div>
                </div>
              </div>
            @else
              <span class="text-muted fs-13">UID: {{ $log->biometric_user_id }}</span>
              <span class="badge badge-danger" style="font-size:10px">Unmapped</span>
            @endif
          </td>
          <td><code style="font-size:11px">{{ $log->device_serial }}</code></td>
          <td class="fs-13">
            <strong>{{ \Carbon\Carbon::parse($log->punch_time)->format('d M Y') }}</strong><br>
            <span class="text-muted" style="font-size:11px">{{ \Carbon\Carbon::parse($log->punch_time)->format('h:i:s A') }}</span>
          </td>
          <td>
            @if($log->punch_type === 'out')
              <span class="badge badge-danger"><i class="bi bi-box-arrow-right"></i> OUT</span>
            @else
              <span class="badge badge-success"><i class="bi bi-box-arrow-in-right"></i> IN</span>
            @endif
          </td>
          <td class="text-muted fs-13">
            {{ ['0'=>'Fingerprint','1'=>'Password','4'=>'Card','15'=>'Face'][$log->verify_type] ?? 'Unknown' }}
          </td>
          <td>
            @if($log->processed)
              <span class="badge badge-success">Processed</span>
            @else
              <span class="badge badge-warning">Pending</span>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">📡</div><h3>No punch logs found for this period</h3><p class="text-muted fs-13">Connect a ZKTeco device and configure it to push to {{ url('/adms') }}</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:14px 18px">{{ $logs->withQueryString()->links() }}</div>
</div>
@endsection
