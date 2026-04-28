@extends('layouts.app')
@section('title','Edit Supplier')
@section('breadcrumb')
  <a href="{{ route('inventory.suppliers.index') }}">Suppliers</a> &rsaquo; <span class="current">Edit</span>
@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Edit Supplier</h1><p class="page-subtitle">{{ $supplier->name }}</p></div>
  <a href="{{ route('inventory.suppliers.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="glass-card" style="max-width:700px;margin:0 auto">
  <form method="POST" action="{{ route('inventory.suppliers.update', $supplier) }}">
    @csrf @method('PUT')
    <div class="card-body">
      <div class="grid g-2 gap-12">
        <div class="form-group">
          <label class="form-label">Company/Supplier Name <span class="req">*</span></label>
          <input type="text" name="name" class="form-control" value="{{ $supplier->name }}" required>
        </div>
        <div class="form-group">
          <label class="form-label">Contact Person</label>
          <input type="text" name="contact_person" class="form-control" value="{{ $supplier->contact_person }}">
        </div>
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" value="{{ $supplier->phone }}">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="{{ $supplier->email }}">
        </div>
      </div>
      <div class="form-group mt-12">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="2">{{ $supplier->address }}</textarea>
      </div>
      <div class="form-group mt-12" style="display:flex;align-items:center;gap:8px;">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" id="is_active" {{ $supplier->is_active ? 'checked' : '' }} style="width:16px;height:16px">
        <label for="is_active" class="form-label" style="margin:0">Active Status</label>
      </div>
    </div>
    <div style="padding:16px 20px;border-top:1px solid var(--clr-border);display:flex;justify-content:flex-end">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Supplier</button>
    </div>
  </form>
</div>
@endsection
