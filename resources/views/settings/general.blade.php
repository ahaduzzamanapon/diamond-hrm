@extends('layouts.app')
@section('title','General Settings')
@section('breadcrumb')<span class="current">Settings — General</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">General Settings</h1><p class="page-subtitle">Company information and system preferences</p></div>
</div>

{{-- Sub-nav --}}
<div class="flex gap-8 mb-20">
  <a href="{{ route('settings.general') }}" class="btn btn-primary">General</a>
  <a href="{{ route('settings.leave') }}" class="btn btn-secondary">Leave</a>
</div>

@if(session('success'))
  <div class="alert alert-success mb-16"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('settings.general.update') }}" enctype="multipart/form-data">
  @csrf

  {{-- Company Info --}}
  <div class="glass-card mb-16">
    <div class="card-header"><div class="card-title"><i class="bi bi-building"></i> Company Information</div></div>
    <div class="card-body">
      <div class="grid g-2 gap-12">
        <div class="form-group">
          <label class="form-label">Company Name <span class="req">*</span></label>
          <input type="text" name="company_name" class="form-control" value="{{ $settings['company_name'] ?? '' }}" placeholder="Diamond World">
        </div>
        <div class="form-group">
          <label class="form-label">Tagline</label>
          <input type="text" name="company_tagline" class="form-control" value="{{ $settings['company_tagline'] ?? '' }}" placeholder="Your company motto">
        </div>
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="text" name="company_phone" class="form-control" value="{{ $settings['company_phone'] ?? '' }}" placeholder="+880...">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="company_email" class="form-control" value="{{ $settings['company_email'] ?? '' }}" placeholder="info@company.com">
        </div>
      </div>
      <div class="form-group mt-12">
        <label class="form-label">Address</label>
        <textarea name="company_address" class="form-control" rows="2" placeholder="Full company address">{{ $settings['company_address'] ?? '' }}</textarea>
      </div>
      <div class="form-group mt-12">
        <label class="form-label">Company Logo</label>
        @if(!empty($settings['company_logo']))
          <div class="mb-8">
            <img src="{{ asset('storage/'.$settings['company_logo']) }}" style="height:60px;border-radius:8px;border:1px solid var(--clr-border)">
          </div>
        @endif
        <input type="file" name="company_logo" class="form-control" accept="image/*">
        <div class="text-muted fs-12 mt-4">PNG/JPG recommended. Max 2MB.</div>
      </div>
    </div>
  </div>

  {{-- Currency --}}
  <div class="glass-card mb-16">
    <div class="card-header"><div class="card-title"><i class="bi bi-currency-exchange"></i> Currency</div></div>
    <div class="card-body">
      <div class="grid g-2 gap-12">
        <div class="form-group">
          <label class="form-label">Currency Symbol</label>
          <input type="text" name="currency_symbol" class="form-control" value="{{ $settings['currency_symbol'] ?? '৳' }}" placeholder="৳">
        </div>
        <div class="form-group">
          <label class="form-label">Currency Code</label>
          <input type="text" name="currency_code" class="form-control" value="{{ $settings['currency_code'] ?? 'BDT' }}" placeholder="BDT">
        </div>
      </div>
    </div>
  </div>

  <div style="display:flex;justify-content:flex-end">
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save General Settings</button>
  </div>
</form>
@endsection
