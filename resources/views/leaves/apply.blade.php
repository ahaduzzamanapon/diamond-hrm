@extends('layouts.app')
@section('title','Apply Leave')
@section('breadcrumb')<a href="{{ route('leaves.index') }}">Leave</a> &rsaquo; <span class="current">Apply</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Apply Leave</h1><p class="page-subtitle">Submit a new leave application</p></div>
  <a href="{{ route('leaves.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="glass-card" style="max-width:700px;margin:0 auto">
  <div class="card-header"><div class="card-title"><i class="bi bi-calendar-minus"></i> Leave Application Form</div></div>
  <form method="POST" action="{{ route('leaves.apply.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger mb-16">
          <ul style="margin:0;padding-left:18px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <div class="grid g-2 gap-12">
        <div class="form-group">
          <label class="form-label">Employee <span class="req">*</span></label>
          @if($employees->count() === 1)
            <input type="hidden" name="employee_id" value="{{ $employees->first()->id }}">
            <input type="text" class="form-control" value="{{ $employees->first()->name }}" readonly style="background:var(--clr-bg-alt);cursor:not-allowed;color:var(--clr-text-muted)">
          @else
            <select name="employee_id" class="form-control" required>
              <option value="">Select Employee</option>
              @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ old('employee_id')==$emp->id?'selected':'' }}>
                  {{ $emp->name }} ({{ $emp->employee_id }})
                </option>
              @endforeach
            </select>
          @endif
        </div>
        <div class="form-group">
          <label class="form-label">Leave Type <span class="req">*</span></label>
          <select name="leave_type_id" class="form-control" required>
            <option value="">Select Type</option>
            @foreach($leaveTypes as $lt)
              <option value="{{ $lt->id }}" {{ old('leave_type_id')==$lt->id?'selected':'' }}>
                {{ $lt->name }} ({{ $lt->days_per_year }} days/yr)
              </option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">From Date <span class="req">*</span></label>
          <input type="date" name="from_date" class="form-control" value="{{ old('from_date') }}" required>
        </div>
        <div class="form-group">
          <label class="form-label">To Date <span class="req">*</span></label>
          <input type="date" name="to_date" class="form-control" value="{{ old('to_date') }}" required>
        </div>
      </div>

      <div class="form-group mt-12">
        <label class="form-label">Reason <span class="req">*</span></label>
        <textarea name="reason" class="form-control" rows="4" required placeholder="State your reason for leave...">{{ old('reason') }}</textarea>
      </div>

      <div class="form-group mt-12">
        <label class="form-label">Supporting Document <span class="text-muted fs-12">(optional — PDF/image)</span></label>
        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
      </div>
    </div>
    <div class="modal-footer" style="padding:14px 20px;border-top:1px solid var(--clr-border);display:flex;gap:8px;justify-content:flex-end">
      <a href="{{ route('leaves.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Submit Application</button>
    </div>
  </form>
</div>
@endsection
