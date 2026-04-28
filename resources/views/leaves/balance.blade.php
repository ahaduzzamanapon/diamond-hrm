@extends('layouts.app')
@section('title','Leave Balance')
@section('breadcrumb')<a href="{{ route('leaves.index') }}">Leave</a> &rsaquo; <span class="current">Balance</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Leave Balance</h1><p class="page-subtitle">{{ $employee->name }} — {{ $year }}</p></div>
  <form method="GET" class="flex gap-8">
    <select name="year" class="form-control" onchange="this.form.submit()">
      @for($y = now()->year; $y >= now()->year-3; $y--)
        <option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>
      @endfor
    </select>
  </form>
</div>

<div class="grid g-3 gap-16 mb-20">
  @foreach($balances as $b)
  @php
    $pct = $b['allowed'] > 0 ? round(($b['used']/$b['allowed'])*100) : 0;
    $pctColor = $pct >= 90 ? '#ef4444' : ($pct >= 60 ? '#f59e0b' : '#10b981');
  @endphp
  <div class="glass-card" style="padding:20px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <div>
        <div class="fw-700 fs-14">{{ $b['type']->name }}</div>
        <div class="text-muted fs-12">{{ $b['type']->days_per_year }} days per year</div>
      </div>
      <div style="text-align:right">
        <div style="font-size:28px;font-weight:900;color:{{ $pctColor }}">{{ $b['remaining'] }}</div>
        <div class="text-muted fs-11">days left</div>
      </div>
    </div>
    <div style="background:#e2e8f0;border-radius:99px;height:8px;overflow:hidden">
      <div style="width:{{ $pct }}%;height:100%;background:{{ $pctColor }};border-radius:99px;transition:width .3s"></div>
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:11px;color:var(--text-muted)">
      <span>Used: <strong>{{ $b['used'] }}</strong></span>
      <span>Remaining: <strong style="color:{{ $pctColor }}">{{ $b['remaining'] }}</strong></span>
      <span>Total: <strong>{{ $b['allowed'] }}</strong></span>
    </div>
  </div>
  @endforeach
</div>

@if($balances->isEmpty())
<div class="glass-card"><div class="empty-state"><div class="empty-icon">📋</div><h3>No leave types configured</h3><p class="text-muted fs-13">Ask HR to set up leave types in Settings.</p></div></div>
@endif
@endsection
