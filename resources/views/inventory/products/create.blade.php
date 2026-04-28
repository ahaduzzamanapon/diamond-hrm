@extends('layouts.app')
@section('title','Add Product')
@section('breadcrumb')
  <a href="{{ route('inventory.products.index') }}">Products</a> &rsaquo; <span class="current">Add</span>
@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Add Product</h1><p class="page-subtitle">Create a new inventory item</p></div>
  <a href="{{ route('inventory.products.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="glass-card" style="max-width:800px;margin:0 auto">
  <form method="POST" action="{{ route('inventory.products.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="card-body">
      <div class="grid g-2 gap-16 mb-16">
        <div class="form-group">
          <label class="form-label">Product Name <span class="req">*</span></label>
          <input type="text" name="name" class="form-control" required placeholder="e.g. A4 Paper, Dell Mouse">
        </div>
        <div class="form-group">
          <label class="form-label">SKU / Item Code</label>
          <input type="text" name="sku" class="form-control" placeholder="e.g. STAT-001">
        </div>
      </div>

      <div class="grid g-2 gap-16 mb-16">
        <div class="form-group">
          <label class="form-label">Category <span class="req">*</span></label>
          <select name="inventory_category_id" class="form-control" required>
            <option value="">Select Category</option>
            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Unit <span class="req">*</span></label>
          <select name="inventory_unit_id" class="form-control" required>
            <option value="">Select Unit</option>
            @foreach($units as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
          </select>
        </div>
      </div>

      <div class="grid g-2 gap-16 mb-16">
        <div class="form-group">
          <label class="form-label">Opening Stock</label>
          <input type="number" name="current_stock" class="form-control" value="0" min="0">
        </div>
        <div class="form-group">
          <label class="form-label">Low Stock Alert Quantity</label>
          <input type="number" name="alert_quantity" class="form-control" value="5" min="0">
        </div>
      </div>

      <div class="form-group mb-16">
        <label class="form-label">Product Image</label>
        <input type="file" name="image" class="form-control" accept="image/*">
      </div>

      <div class="form-group mt-12">
        <label class="form-label">Description / Specs</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
      </div>
    </div>
    <div style="padding:16px 20px;border-top:1px solid var(--clr-border);display:flex;justify-content:flex-end">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Product</button>
    </div>
  </form>
</div>
@endsection
