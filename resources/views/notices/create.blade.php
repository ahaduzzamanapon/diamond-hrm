@extends('layouts.app')
@section('title','Post Notice')
@section('breadcrumb')<a href="{{ route('notices.index') }}">Notice Board</a> &rsaquo; <span class="current">Post Notice</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Post Notice</h1><p class="page-subtitle">Create a new announcement for your team</p></div>
  <a href="{{ route('notices.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="glass-card" style="max-width:800px;margin:0 auto">
  <div class="card-header"><div class="card-title"><i class="bi bi-megaphone"></i> Notice Details</div></div>
  <form method="POST" action="{{ route('notices.store') }}">
    @csrf
    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger mb-16"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
      @endif

      <div class="form-group">
        <label class="form-label">Title <span class="req">*</span></label>
        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Notice subject">
      </div>

      <div class="grid g-3 gap-12 mt-12">
        <div class="form-group">
          <label class="form-label">Type <span class="req">*</span></label>
          <select name="type" class="form-control" required>
            <option value="">Select Type</option>
            @foreach(['general'=>'General','urgent'=>'Urgent','event'=>'Event','policy'=>'Policy'] as $v=>$l)
              <option value="{{ $v }}" {{ old('type')==$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Audience</label>
          <select name="audience" class="form-control">
            <option value="all">All Staff</option>
            <option value="branch" {{ old('audience')=='branch'?'selected':'' }}>Branch Only</option>
            <option value="department" {{ old('audience')=='department'?'selected':'' }}>Department Only</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Branch <span class="text-muted fs-12">(optional)</span></label>
          <select name="branch_id" class="form-control">
            <option value="">All Branches</option>
            @foreach($branches as $b)<option value="{{ $b->id }}" {{ old('branch_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Published At</label>
          <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}">
        </div>
        <div class="form-group">
          <label class="form-label">Expires At <span class="text-muted fs-12">(optional)</span></label>
          <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
        </div>
        <div class="form-group" style="display:flex;align-items:center;gap:8px;padding-top:24px">
          <input type="checkbox" name="is_published" value="1" id="is_published" {{ old('is_published',1)?'checked':'' }} style="width:18px;height:18px">
          <label for="is_published" class="form-label" style="margin:0">Publish immediately</label>
        </div>
      </div>

      <div class="form-group mt-12">
        <label class="form-label">Body / Content <span class="req">*</span></label>
        <textarea name="body" class="form-control" rows="8" required placeholder="Write your notice here...">{{ old('body') }}</textarea>
      </div>
    </div>
    <div style="padding:14px 20px;border-top:1px solid var(--clr-border);display:flex;gap:8px;justify-content:flex-end">
      <a href="{{ route('notices.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Post Notice</button>
    </div>
  </form>
</div>
@endsection
