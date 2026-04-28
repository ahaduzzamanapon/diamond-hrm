@extends('layouts.app')
@section('title','Manual Attendance')
@section('breadcrumb')<a href="{{ route('attendance.index') }}">Attendance</a><span class="sep">/</span><span class="current">Manual Entry</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Manual Attendance Entry</h1><p class="page-subtitle">Record attendance manually for employees</p></div>
</div>

@if(session('success'))
<div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

<div class="glass-card" style="max-width:600px">
  <div class="card-header"><div class="card-title"><i class="bi bi-pencil-square"></i> Attendance Form</div></div>
  <div class="card-body">
    <form method="POST" action="{{ route('attendance.manual') }}">
      @csrf
      <div class="form-group">
        <label class="form-label">Employee <span class="req">*</span></label>
        <select name="employee_id" class="form-control" required>
          <option value="">Select Employee</option>
          @foreach($employees as $emp)
            <option value="{{ $emp->id }}">{{ $emp->name }} — {{ $emp->employee_id }} ({{ $emp->branch?->name }})</option>
          @endforeach
        </select>
      </div>
      <div class="grid g-2 gap-12">
        <div class="form-group">
          <label class="form-label">Date <span class="req">*</span></label>
          <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="form-group">
          <label class="form-label">Status <span class="req">*</span></label>
          <select name="status" class="form-control" required>
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="late">Late</option>
            <option value="half_day">Half Day</option>
            <option value="leave">On Leave</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Check In Time <span class="req">*</span></label>
          <input type="time" name="in_time" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Check Out Time</label>
          <input type="time" name="out_time" class="form-control">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Note</label>
        <textarea name="note" class="form-control" rows="2" placeholder="Optional note..."></textarea>
      </div>
      <div class="flex gap-8 mt-16">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Attendance</button>
        <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
