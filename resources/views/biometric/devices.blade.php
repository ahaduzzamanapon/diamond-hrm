@extends('layouts.app')
@section('title','Biometric Devices')
@section('breadcrumb')<span class="current">Biometric Devices</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Biometric Devices</h1><p class="page-subtitle">ZKTeco K40 device management and punch logs</p></div>
  <div class="flex gap-8">
    <a href="{{ route('biometric.logs') }}" class="btn btn-secondary"><i class="bi bi-list-ul"></i> View Punch Logs</a>
    <a href="{{ route('biometric.sync') }}" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Sync All</a>
  </div>
</div>

{{-- ADMS Status --}}
<div class="glass-card mb-20">
  <div class="card-header">
    <div class="card-title"><i class="bi bi-wifi" style="color:var(--success)"></i> ADMS Push Server</div>
    <span class="badge badge-success">Active</span>
  </div>
  <div class="card-body">
    <div class="grid g-3 gap-12">
      <div style="background:#f8fafc;border-radius:8px;padding:14px">
        <div class="text-muted fs-13 mb-2">ADMS Endpoint URL</div>
        <code style="font-size:12px;color:var(--text-primary)">{{ url('/api/adms') }}</code>
      </div>
      <div style="background:#f8fafc;border-radius:8px;padding:14px">
        <div class="text-muted fs-13 mb-2">Device Protocol</div>
        <div class="fw-600">ZKTeco ADMS (HTTP Push)</div>
      </div>
      <div style="background:#f8fafc;border-radius:8px;padding:14px">
        <div class="text-muted fs-13 mb-2">Total Punch Logs</div>
        <div class="fw-600 fs-13">{{ $totalLogs ?? 0 }} records</div>
      </div>
    </div>
  </div>
</div>

{{-- Devices Table --}}
<div class="glass-card">
  <div class="card-header">
    <div class="card-title"><i class="bi bi-fingerprint"></i> Registered Devices</div>
    <button class="btn btn-sm btn-primary" onclick="document.getElementById('addDeviceModal').classList.add('open')"><i class="bi bi-plus-lg"></i> Add Device</button>
  </div>
  <div class="table-wrapper">
    <table>
      <thead><tr><th>#</th><th>Device Name</th><th>Serial No.</th><th>Branch</th><th>IP Address</th><th>Last Sync</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($devices as $device)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td><strong>{{ $device->name }}</strong></td>
          <td><code style="font-size:12px">{{ $device->serial_number }}</code></td>
          <td>{{ $device->branch?->name ?? '—' }}</td>
          <td class="fs-13">{{ $device->ip_address ?? '—' }}</td>
          <td class="text-muted fs-13">{{ $device->last_sync_at ? \Carbon\Carbon::parse($device->last_sync_at)->diffForHumans() : 'Never' }}</td>
          <td><span class="badge {{ $device->is_active ? 'badge-success' : 'badge-danger' }}">{{ $device->is_active ? 'Online' : 'Offline' }}</span></td>
          <td>
            <div class="flex gap-8">
              <a href="{{ route('biometric.sync') }}?device={{ $device->id }}" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-repeat"></i></a>
              <form method="POST" action="{{ route('biometric.devices.destroy', $device) }}" onsubmit="return confirm('Remove device?')">@csrf @method('DELETE')
                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="8">
          <div class="empty-state">
            <div class="empty-icon">🔐</div>
            <h3>No biometric devices registered</h3>
            <p class="text-muted fs-13">Add a device or configure your ZKTeco K40 to push to the ADMS endpoint above.</p>
          </div>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Add Device Modal --}}
<div class="modal-overlay" id="addDeviceModal">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Register Biometric Device</span><button class="modal-close" onclick="document.getElementById('addDeviceModal').classList.remove('open')">&times;</button></div>
    <form method="POST" action="{{ route('biometric.devices.store') }}">@csrf
      <div class="modal-body">
        <div class="grid g-2 gap-12">
          <div class="form-group"><label class="form-label">Device Name <span class="req">*</span></label><input name="name" class="form-control" required placeholder="e.g. Gate 1 Device"></div>
          <div class="form-group"><label class="form-label">Serial Number <span class="req">*</span></label><input name="serial_number" class="form-control" required placeholder="ZKTeco serial no."></div>
          <div class="form-group"><label class="form-label">IP Address</label><input name="ip_address" class="form-control" placeholder="192.168.1.100"></div>
          <div class="form-group"><label class="form-label">Branch</label>
            <select name="branch_id" class="form-control"><option value="">Select Branch</option>@foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select>
          </div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('addDeviceModal').classList.remove('open')">Cancel</button><button type="submit" class="btn btn-primary">Register</button></div>
    </form>
  </div>
</div>
@endsection
