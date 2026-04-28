@extends('layouts.app')
@section('title','Edit Product')
@section('breadcrumb')
  <a href="{{ route('inventory.products.index') }}">Products</a> &rsaquo; <span class="current">Edit</span>
@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Edit Product</h1><p class="page-subtitle">{{ $product->name }}</p></div>
  <a href="{{ route('inventory.products.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="glass-card" style="max-width:800px;margin:0 auto">
  <form method="POST" action="{{ route('inventory.products.update', $product) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="card-body">
      @if($product->image)
        <div class="mb-16">
          <img src="{{ asset('storage/'.$product->image) }}" style="height:80px;border-radius:8px;border:1px solid var(--clr-border);">
        </div>
      @endif

      <div class="grid g-2 gap-16 mb-16">
        <div class="form-group">
          <label class="form-label">Product Name <span class="req">*</span></label>
          <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
        </div>
        <div class="form-group">
          <label class="form-label">SKU / Item Code</label>
          <input type="text" name="sku" class="form-control" value="{{ $product->sku }}">
        </div>
      </div>

      <div class="grid g-2 gap-16 mb-16">
        <div class="form-group">
          <label class="form-label">Category <span class="req">*</span></label>
          <select name="inventory_category_id" class="form-control" required>
            @foreach($categories as $c)<option value="{{ $c->id }}" {{ $product->inventory_category_id == $c->id ? 'selected':'' }}>{{ $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Unit <span class="req">*</span></label>
          <select name="inventory_unit_id" class="form-control" required>
            @foreach($units as $u)<option value="{{ $u->id }}" {{ $product->inventory_unit_id == $u->id ? 'selected':'' }}>{{ $u->name }}</option>@endforeach
          </select>
        </div>
      </div>

      <div class="grid g-2 gap-16 mb-16">
        <div class="form-group">
          <label class="form-label">Current Stock <span class="text-muted fs-11">(Usually updated via purchase/requisition)</span></label>
          <input type="number" name="current_stock" class="form-control" value="{{ $product->current_stock }}" min="0">
        </div>
        <div class="form-group">
          <label class="form-label">Low Stock Alert Quantity</label>
          <input type="number" name="alert_quantity" class="form-control" value="{{ $product->alert_quantity }}" min="0">
        </div>
      </div>

      <div class="form-group mb-16">
        <label class="form-label">Replace Image</label>
        <input type="file" name="image" class="form-control" accept="image/*">
      </div>

      <div class="form-group mt-12">
        <label class="form-label">Description / Specs</label>
        <textarea name="description" class="form-control" rows="3">{{ $product->description }}</textarea>
      </div>

      <div class="form-group mt-16" style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox" name="is_active" value="1" id="is_active" {{ $product->is_active ? 'checked' : '' }} style="width:16px;height:16px">
        <label for="is_active" class="form-label" style="margin:0">Active Status</label>
      </div>
    </div>
    <div style="padding:16px 20px;border-top:1px solid var(--clr-border);display:flex;justify-content:flex-end">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Product</button>
    </div>
  </form>
</div>
@endsection
