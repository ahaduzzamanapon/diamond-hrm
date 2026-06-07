@extends('layouts.app')
@section('title','Payroll Settings')
@section('breadcrumb')<span class="current">Settings — Payroll</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Payroll Settings</h1><p class="page-subtitle">Configure monthly working days, overtime rates, and deductions</p></div>
</div>

{{-- Sub-nav --}}
<div class="flex gap-8 mb-20">
  <a href="{{ route('settings.general') }}" class="btn btn-secondary">General</a>
  <a href="{{ route('settings.leave') }}" class="btn btn-secondary">Leave</a>
  <a href="{{ route('settings.payroll') }}" class="btn btn-primary">Payroll</a>
</div>

@if(session('success'))
  <div class="alert alert-success mb-16"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('settings.payroll.update') }}">
  @csrf
  <div class="glass-card">
    <div class="card-header"><div class="card-title"><i class="bi bi-cash-stack"></i> Payroll Configurations</div></div>
    <div class="card-body">
      <div class="grid g-2 gap-16">
        
        {{-- Working Days --}}
        <div class="form-group">
          <label class="form-label">Default Working Days Per Month</label>
          <input type="number" name="working_days_month" class="form-control" 
            value="{{ $settings['working_days_month'] ?? '26' }}" min="1" max="31" required>
          <div class="text-muted fs-11 mt-4">Used as a fallback divisor for daily rate calculation if no attendance exists.</div>
        </div>

        {{-- Late Deduction --}}
        <div class="form-group">
          <label class="form-label">Late Days Per Unpaid Absent Day</label>
          <input type="number" name="late_deduction" class="form-control" 
            value="{{ $settings['late_deduction'] ?? '3' }}" min="1" max="31" required>
          <div class="text-muted fs-11 mt-4">Number of late arrivals that trigger 1 day absent salary deduction (e.g. 3 lates = 1 absent).</div>
        </div>

        {{-- Overtime Rate --}}
        <div class="form-group" x-data="{ otEnabled: {{ ($settings['overtime_enabled'] ?? '0') === '1' ? 'true' : 'false' }} }">
          <label class="form-label">Overtime Options</label>
          <div style="display:flex;align-items:center;gap:12px;background:#f8fafc;padding:12px;border-radius:8px;margin-bottom:12px">
            <input type="hidden" name="overtime_enabled" value="0">
            <input type="checkbox" name="overtime_enabled" value="1" id="ot_enabled"
              x-model="otEnabled"
              style="width:18px;height:18px;cursor:pointer">
            <label for="ot_enabled" class="fw-600 fs-13" style="cursor:pointer;margin:0">Enable Overtime Calculations</label>
          </div>
          
          <div x-show="otEnabled" x-transition>
            <label class="form-label">Overtime Rate Multiplier</label>
            <input type="number" name="overtime_rate" class="form-control" step="0.1"
              value="{{ $settings['overtime_rate'] ?? '1.5' }}" min="1.0">
            <div class="text-muted fs-11 mt-4">Multiplier applied to hourly rate for overtime hours (e.g., 1.5x basic rate).</div>
          </div>
        </div>

        {{-- Eid Bonus --}}
        <div class="form-group">
          <label class="form-label">Eid / Festival Bonus</label>
          <div style="display:flex;align-items:center;gap:12px;background:#f8fafc;padding:12px;border-radius:8px">
            <input type="hidden" name="eid_bonus_enabled" value="0">
            <input type="checkbox" name="eid_bonus_enabled" value="1" id="eid_bonus"
              {{ ($settings['eid_bonus_enabled'] ?? '0') === '1' ? 'checked' : '' }}
              style="width:18px;height:18px;cursor:pointer">
            <label for="eid_bonus" class="fw-600 fs-13" style="cursor:pointer;margin:0">Enable Eid / Festival Bonus Processing</label>
          </div>
          <div class="text-muted fs-11 mt-4">Enables additional festival bonus components in monthly payouts.</div>
        </div>

      </div>
    </div>
    <div style="padding:14px 20px;border-top:1px solid var(--clr-border);display:flex;justify-content:flex-end">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Settings</button>
    </div>
  </div>
</form>
@endsection
