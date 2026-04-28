@extends('layouts.app')
@section('title','Employee — '.$employee->name)
@section('breadcrumb')<a href="{{ route('employees.index') }}">Employees</a> &rsaquo; <span class="current">{{ $employee->name }}</span>@endsection

@section('content')
<div class="page-header">
  <div style="display:flex;align-items:center;gap:16px">
    <img src="{{ $employee->photo_url }}" class="avatar" style="width:56px;height:56px;border-radius:12px">
    <div>
      <h1 class="page-title" style="margin:0">{{ $employee->name }}</h1>
      <p class="page-subtitle" style="margin:0">{{ $employee->designation?->name ?? '—' }} &bull; {{ $employee->employee_id }}</p>
    </div>
  </div>
  <div class="flex gap-8">
    <a href="{{ route('employees.transfers.index', $employee) }}" class="btn btn-warning">
      <i class="bi bi-arrow-left-right"></i> Transfer
    </a>
    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit</a>
    <a href="{{ route('employees.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

</div>

{{-- Status Banner --}}
<div style="margin-bottom:16px">
  <span class="badge {{ match($employee->status){'active'=>'badge-success','inactive'=>'badge-warning','terminated'=>'badge-danger',default=>'badge-secondary'} }}" style="font-size:13px;padding:6px 14px">
    {{ ucfirst($employee->status) }}
  </span>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
  {{-- Left Column --}}
  <div>
    {{-- Personal Info --}}
    <div class="glass-card mb-16">
      <div class="card-header"><div class="card-title"><i class="bi bi-person"></i> Personal Information</div></div>
      <div class="card-body">
        <table style="width:100%;font-size:13px;line-height:1.9">
          <tr><td class="text-muted" style="width:45%">Full Name</td><td class="fw-600">{{ $employee->name }}</td></tr>
          <tr><td class="text-muted">Email</td><td>{{ $employee->email ?? '—' }}</td></tr>
          <tr><td class="text-muted">Phone</td><td>{{ $employee->contact_number ?? '—' }}</td></tr>
          <tr><td class="text-muted">Gender</td><td>{{ ucfirst($employee->gender ?? '—') }}</td></tr>
          <tr><td class="text-muted">Blood Group</td><td>{{ $employee->blood_group ?? '—' }}</td></tr>
          <tr><td class="text-muted">Date of Birth</td><td>{{ $employee->date_of_birth?->format('d M Y') ?? '—' }}</td></tr>
          <tr><td class="text-muted">NID</td><td>{{ $employee->nid ?? '—' }}</td></tr>
          <tr><td class="text-muted">Address</td><td>{{ $employee->address ?? '—' }}</td></tr>
        </table>
      </div>
    </div>

    {{-- Employment --}}
    <div class="glass-card mb-16">
      <div class="card-header"><div class="card-title"><i class="bi bi-briefcase"></i> Employment</div></div>
      <div class="card-body">
        <table style="width:100%;font-size:13px;line-height:1.9">
          <tr><td class="text-muted" style="width:45%">Branch</td><td class="fw-600">{{ $employee->branch?->name ?? '—' }}</td></tr>
          <tr><td class="text-muted">Department</td><td>{{ $employee->department?->name ?? '—' }}</td></tr>
          <tr><td class="text-muted">Designation</td><td>{{ $employee->designation?->name ?? '—' }}</td></tr>
          <tr><td class="text-muted">Shift</td><td>{{ $employee->shift?->name ?? '—' }}</td></tr>
          <tr><td class="text-muted">Joining Date</td><td>{{ $employee->joining_date?->format('d M Y') ?? '—' }}</td></tr>
          <tr><td class="text-muted">Probation</td><td>{{ $employee->probation_months ?? 0 }} months</td></tr>
          <tr><td class="text-muted">Type</td><td>{{ ucfirst($employee->employee_type ?? 'employee') }}</td></tr>
          <tr><td class="text-muted">Biometric ID</td><td>{{ $employee->biometric_user_id ?? '—' }}</td></tr>
          <tr><td class="text-muted">System Role</td><td>{{ $employee->user?->roles->first()?->name ?? '—' }}</td></tr>
        </table>
      </div>
    </div>

    {{-- Salary --}}
    <div class="glass-card mb-16">
      <div class="card-header"><div class="card-title"><i class="bi bi-cash-stack"></i> Salary</div></div>
      <div class="card-body">
        <table style="width:100%;font-size:13px;line-height:1.9">
          <tr><td class="text-muted" style="width:45%">Basic Salary</td><td class="fw-600">৳{{ number_format($employee->basic_salary) }}</td></tr>
          <tr><td class="text-muted">House Rent</td><td>৳{{ number_format($employee->house_rent_allowance) }}</td></tr>
          <tr><td class="text-muted">Medical</td><td>৳{{ number_format($employee->medical_allowance) }}</td></tr>
          <tr><td class="text-muted">Transport</td><td>৳{{ number_format($employee->transport_allowance) }}</td></tr>
          <tr style="border-top:1px solid var(--clr-border)"><td class="text-muted">Gross Total</td><td class="fw-700" style="color:var(--success)">৳{{ number_format($employee->basic_salary + $employee->house_rent_allowance + $employee->medical_allowance + $employee->transport_allowance) }}</td></tr>
          <tr><td class="text-muted">Bank</td><td>{{ $employee->bank_name ?? '—' }}</td></tr>
          <tr><td class="text-muted">Account No.</td><td><code style="font-size:11px">{{ $employee->bank_account ?? '—' }}</code></td></tr>
        </table>
      </div>
    </div>
  </div>

  {{-- Right Column --}}
  <div>
    {{-- Emergency Contact --}}
    @if($employee->emergencyContacts->count())
    <div class="glass-card mb-16">
      <div class="card-header"><div class="card-title"><i class="bi bi-telephone-outbound"></i> Emergency Contact</div></div>
      <div class="card-body">
        @foreach($employee->emergencyContacts as $ec)
        <table style="width:100%;font-size:13px;line-height:1.9">
          <tr><td class="text-muted" style="width:40%">Name</td><td class="fw-600">{{ $ec->name }}</td></tr>
          <tr><td class="text-muted">Relation</td><td>{{ $ec->relation ?? '—' }}</td></tr>
          <tr><td class="text-muted">Phone</td><td>{{ $ec->phone ?? '—' }}</td></tr>
        </table>
        @endforeach
      </div>
    </div>
    @endif

    {{-- Recent Attendance --}}
    <div class="glass-card mb-16">
      <div class="card-header">
        <div class="card-title"><i class="bi bi-calendar-check"></i> Recent Attendance</div>
        <a href="{{ route('attendance.index', ['search'=>$employee->employee_id]) }}" class="btn btn-sm btn-secondary">View All</a>
      </div>
      <div class="table-wrapper">
        <table>
          <thead><tr><th>Date</th><th>In</th><th>Out</th><th>Status</th></tr></thead>
          <tbody>
            @forelse($employee->attendances as $att)
            <tr>
              <td class="fs-12">{{ \Carbon\Carbon::parse($att->date)->format('d M') }}</td>
              <td class="fs-12">{{ $att->in_time ? \Carbon\Carbon::parse($att->in_time)->format('h:i A') : '—' }}</td>
              <td class="fs-12">{{ $att->out_time ? \Carbon\Carbon::parse($att->out_time)->format('h:i A') : '—' }}</td>
              <td><span class="badge att-{{ $att->status }} fs-12">{{ ucfirst($att->status) }}</span></td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-muted fs-13" style="padding:12px;text-align:center">No records</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Recent Payrolls --}}
    <div class="glass-card mb-16">
      <div class="card-header">
        <div class="card-title"><i class="bi bi-cash-coin"></i> Recent Payslips</div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead><tr><th>Month</th><th style="text-align:right">Net</th><th>Status</th><th></th></tr></thead>
          <tbody>
            @forelse($employee->payrolls as $p)
            <tr>
              <td class="fs-13">{{ \Carbon\Carbon::parse($p->month.'-01')->format('M Y') }}</td>
              <td class="fs-13 fw-600" style="text-align:right;color:var(--success)">৳{{ number_format($p->net_salary) }}</td>
              <td><span class="badge {{ match($p->status??'draft'){'paid'=>'badge-success','approved'=>'badge-info',default=>'badge-secondary'} }} fs-12">{{ ucfirst($p->status??'draft') }}</span></td>
              <td><a href="{{ route('payroll.payslip',$p) }}" class="btn btn-sm btn-secondary" target="_blank"><i class="bi bi-file-pdf"></i></a></td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-muted fs-13" style="padding:12px;text-align:center">No payslips yet</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Recent Leaves --}}
    <div class="glass-card">
      <div class="card-header"><div class="card-title"><i class="bi bi-calendar-x"></i> Recent Leaves</div></div>
      <div class="table-wrapper">
        <table>
          <thead><tr><th>Type</th><th>From</th><th>Days</th><th>Status</th></tr></thead>
          <tbody>
            @forelse($employee->leaves as $lv)
            <tr>
              <td class="fs-12">{{ $lv->leaveType?->name ?? '—' }}</td>
              <td class="fs-12">{{ \Carbon\Carbon::parse($lv->from_date)->format('d M Y') }}</td>
              <td class="fs-12">{{ $lv->total_days }}d</td>
              <td><span class="badge {{ match($lv->status??'pending'){'approved'=>'badge-success','rejected'=>'badge-danger',default=>'badge-warning'} }} fs-12">{{ ucfirst($lv->status) }}</span></td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-muted fs-13" style="padding:12px;text-align:center">No leaves</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
