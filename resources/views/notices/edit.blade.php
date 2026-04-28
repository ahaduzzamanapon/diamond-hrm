@extends('layouts.app')
@section('title','Edit Notice')
@section('breadcrumb')<a href="{{ route('notices.index') }}">Notice Board</a> &rsaquo; <span class="current">Edit</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Edit Notice</h1><p class="page-subtitle">{{ Str::limit($notice->title, 60) }}</p></div>
  <a href="{{ route('notices.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="glass-card" style="max-width:800px;margin:0 auto">
  <div class="card-header"><div class="card-title"><i class="bi bi-pencil-square"></i> Edit Notice</div></div>
  <form method="POST" action="{{ route('notices.update', $notice) }}">
    @csrf @method('PUT')
    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger mb-16"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
      @endif

      <div class="form-group">
        <label class="form-label">Title <span class="req">*</span></label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $notice->title) }}" required>
      </div>

      <div class="grid g-3 gap-12 mt-12">
        <div class="form-group">
          <label class="form-label">Type <span class="req">*</span></label>
          <select name="type" class="form-control" required>
            @foreach(['general'=>'General','urgent'=>'Urgent','event'=>'Event','policy'=>'Policy'] as $v=>$l)
              <option value="{{ $v }}" {{ old('type',$notice->type)==$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Audience</label>
          <select name="audience" class="form-control">
            <option value="all" {{ old('audience',$notice->audience)=='all'?'selected':'' }}>All Staff</option>
            <option value="branch" {{ old('audience',$notice->audience)=='branch'?'selected':'' }}>Branch Only</option>
            <option value="department" {{ old('audience',$notice->audience)=='department'?'selected':'' }}>Department Only</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Branch</label>
          <select name="branch_id" class="form-control">
            <option value="">All Branches</option>
            @foreach($branches as $b)<option value="{{ $b->id }}" {{ old('branch_id',$notice->branch_id)==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Published At</label>
          <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', $notice->published_at?->format('Y-m-d\TH:i')) }}">
        </div>
        <div class="form-group">
          <label class="form-label">Expires At</label>
          <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at', $notice->expires_at?->format('Y-m-d')) }}">
        </div>
        <div class="form-group" style="display:flex;align-items:center;gap:8px;padding-top:24px">
          <input type="checkbox" name="is_published" value="1" id="is_published" {{ old('is_published',$notice->is_published)?'checked':'' }} style="width:18px;height:18px">
          <label for="is_published" class="form-label" style="margin:0">Published</label>
        </div>
      </div>

      <div class="form-group mt-12">
        <label class="form-label">Body / Content <span class="req">*</span></label>
        <textarea name="body" class="form-control" rows="8" required>{{ old('body', $notice->body) }}</textarea>
      </div>
    </div>
    <div style="padding:14px 20px;border-top:1px solid var(--clr-border);display:flex;gap:8px;justify-content:flex-end">
      <a href="{{ route('notices.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Notice</button>
    </div>
  </form>
</div>
@endsection
