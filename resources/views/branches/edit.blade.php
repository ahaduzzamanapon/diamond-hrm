@extends('layouts.app')
@section('title','Edit Branch — '.$branch->name)
@section('breadcrumb')<a href="{{ route('branches.index') }}">Branches</a> &rsaquo; <span class="current">Edit</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Edit Branch</h1><p class="page-subtitle">{{ $branch->name }}</p></div>
  <a href="{{ route('branches.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="glass-card" style="max-width:700px;margin:0 auto">
  <div class="card-header"><div class="card-title"><i class="bi bi-pencil-square"></i> Edit Details</div></div>
  <form method="POST" action="{{ route('branches.update', $branch) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger mb-16"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
      @endif

      @if($branch->logo)
        <div class="mb-12">
          <img src="{{ asset('storage/'.$branch->logo) }}" style="height:60px;border-radius:8px;border:1px solid var(--clr-border)">
        </div>
      @endif

      <div class="grid g-2 gap-12">
        <div class="form-group">
          <label class="form-label">Branch Name <span class="req">*</span></label>
          <input type="text" name="name" class="form-control" value="{{ old('name', $branch->name) }}" required>
        </div>
        <div class="form-group">
          <label class="form-label">Branch Code</label>
          <input type="text" name="code" class="form-control" value="{{ old('code', $branch->code) }}" placeholder="e.g. MAIN">
        </div>
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" value="{{ old('phone', $branch->phone) }}">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="{{ old('email', $branch->email) }}">
        </div>
      </div>
      <div class="form-group mt-12">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="2">{{ old('address', $branch->address) }}</textarea>
      </div>
      <div class="grid g-2 gap-12 mt-12">
        <div class="form-group">
          <label class="form-label">Logo <span class="text-muted fs-12">(leave blank to keep current)</span></label>
          <input type="file" name="logo" class="form-control" accept="image/*">
        </div>
        <div class="form-group" style="display:flex;align-items:center;gap:8px;padding-top:24px">
          <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $branch->is_active ?? true) ? 'checked' : '' }} style="width:18px;height:18px">
          <label for="is_active" class="form-label" style="margin:0">Active</label>
        </div>
      </div>
    </div>
    <div style="padding:14px 20px;border-top:1px solid var(--clr-border);display:flex;gap:8px;justify-content:flex-end">
      <a href="{{ route('branches.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Branch</button>
    </div>
  </form>
</div>
@endsection
