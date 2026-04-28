@extends('layouts.app')
@section('title','Add Supplier')
@section('breadcrumb')
  <a href="{{ route('inventory.suppliers.index') }}">Suppliers</a> &rsaquo; <span class="current">Add</span>
@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Add Supplier</h1><p class="page-subtitle">Create a new vendor profile</p></div>
  <a href="{{ route('inventory.suppliers.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="glass-card" style="max-width:700px;margin:0 auto">
  <form method="POST" action="{{ route('inventory.suppliers.store') }}">
    @csrf
    <div class="card-body">
      <div class="grid g-2 gap-12">
        <div class="form-group">
          <label class="form-label">Company/Supplier Name <span class="req">*</span></label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Contact Person</label>
          <input type="text" name="contact_person" class="form-control">
        </div>
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control">
        </div>
      </div>
      <div class="form-group mt-12">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="2"></textarea>
      </div>
    </div>
    <div style="padding:16px 20px;border-top:1px solid var(--clr-border);display:flex;justify-content:flex-end">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Supplier</button>
    </div>
  </form>
</div>
@endsection
