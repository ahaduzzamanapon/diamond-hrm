@extends('layouts.app')
@section('title','New Short Leave Entry')
@section('breadcrumb')<a href="{{ route('attendance.index') }}">Attendance</a><span class="sep">/</span><a href="{{ route('attendance.short-leave.index') }}">Short Leave</a><span class="sep">/</span><span class="current">New Entry</span>@endsection

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">New Short Leave Entry</h1>
    <p class="page-subtitle">কর্মচারীর দিনের মধ্যে সাময়িক অনুপস্থিতি রেকর্ড করুন</p>
  </div>
  <a href="{{ route('attendance.short-leave.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
</div>

<div class="glass-card" style="max-width:680px">
  <div class="card-header">
    <div class="card-title"><i class="bi bi-clock-history"></i> Short Leave Form</div>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('attendance.short-leave.store') }}" id="slForm">
      @csrf

      <div class="form-group">
        <label class="form-label">Employee <span class="req">*</span></label>
        <select name="employee_id" id="employee_id" class="form-control" required>
          <option value="">Select Employee</option>
          @foreach($employees as $emp)
            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
              {{ $emp->name }} — {{ $emp->employee_id }}
              @if($emp->branch) ({{ $emp->branch->name }}) @endif
            </option>
          @endforeach
        </select>
      </div>

      <div class="grid g-3 gap-12">
        <div class="form-group">
          <label class="form-label">Date <span class="req">*</span></label>
          <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
        </div>
        <div class="form-group">
          <label class="form-label">Out Time <span class="req">*</span></label>
          <input type="time" name="out_time" id="out_time" class="form-control" value="{{ old('out_time') }}" required oninput="calcDuration()">
        </div>
        <div class="form-group">
          <label class="form-label">Return Time <small class="text-muted">(if returned)</small></label>
          <input type="time" name="in_time" id="in_time" class="form-control" value="{{ old('in_time') }}" oninput="calcDuration()">
        </div>
      </div>

      {{-- Duration indicator --}}
      <div id="durationBox" style="display:none;margin-bottom:14px;padding:10px 14px;border-radius:10px;background:#fff7ed;border:1.5px solid #fed7aa;color:#9a3412;font-weight:600;font-size:13px">
        <i class="bi bi-clock"></i> Duration: <span id="durationText"></span>
      </div>

      <div class="form-group">
        <label class="form-label">Reason <span class="req">*</span></label>
        <input type="text" name="reason" class="form-control" value="{{ old('reason') }}"
          placeholder="e.g. Bank, Doctor, Personal work, Emergency..." required maxlength="255">
      </div>

      <div class="form-group">
        <label class="form-label">Note <small class="text-muted">(optional)</small></label>
        <textarea name="note" class="form-control" rows="2" placeholder="Additional details...">{{ old('note') }}</textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Remarks <small class="text-muted">(optional)</small></label>
        <textarea name="remarks" class="form-control" rows="2" placeholder="Enter remarks...">{{ old('remarks') }}</textarea>
      </div>

      <div class="flex gap-8 mt-16">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Entry</button>
        <a href="{{ route('attendance.short-leave.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function calcDuration() {
  const out = document.getElementById('out_time').value;
  const inT = document.getElementById('in_time').value;
  const box  = document.getElementById('durationBox');
  const txt  = document.getElementById('durationText');

  if (!out || !inT) { box.style.display = 'none'; return; }

  const [oh, om] = out.split(':').map(Number);
  const [ih, im] = inT.split(':').map(Number);
  let diff = (ih * 60 + im) - (oh * 60 + om);

  if (diff <= 0) { box.style.display = 'none'; return; }

  const h   = Math.floor(diff / 60);
  const min = diff % 60;
  txt.textContent = h > 0 ? `${h}h ${min}m (${diff} minutes)` : `${min} minutes`;
  box.style.display = 'block';
}
</script>
@endpush
