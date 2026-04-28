@extends('layouts.app')
@section('title','Products')
@section('breadcrumb')<a href="#">Inventory</a> &rsaquo; <span class="current">Products</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Products / Items</h1><p class="page-subtitle">Manage inventory items and current stock</p></div>
  <a href="{{ route('inventory.products.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Add Product</a>
</div>

{{-- Filter --}}
<div class="filter-bar">
  <form method="GET" class="flex gap-8 flex-wrap" style="width:100%">
    <div class="form-group" style="flex:2;min-width:200px">
      <label class="form-label">Search</label>
      <input type="text" name="search" class="form-control" placeholder="Product name or SKU…" value="{{ request('search') }}">
    </div>
    <div class="form-group" style="flex:1;min-width:150px">
      <label class="form-label">Category</label>
      <select name="category_id" class="form-control">
        <option value="">All Categories</option>
        @foreach($categories as $c)
          <option value="{{ $c->id }}" {{ request('category_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-group" style="display:flex;align-items:flex-end;gap:8px;">
      <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin-bottom:8px;">
        <input type="checkbox" name="stock_alert" value="1" {{ request('stock_alert') ? 'checked' : '' }} style="width:16px;height:16px;">
        <span class="fs-13 fw-600">Low Stock</span>
      </label>
    </div>
    <div class="form-group" style="display:flex;align-items:flex-end;gap:8px;min-width:auto">
      <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
      <a href="{{ route('inventory.products.index') }}" class="btn btn-secondary"><i class="bi bi-x-lg"></i></a>
    </div>
  </form>
</div>

<div class="glass-card">
  <div class="table-wrapper">
    <table>
      <thead><tr><th>Product Info</th><th>SKU</th><th>Category</th><th>Current Stock</th><th>Alert Qty</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($products as $p)
        <tr>
          <td>
            <div class="flex gap-8" style="align-items:center">
              @if($p->image)
                <img src="{{ asset('storage/'.$p->image) }}" style="width:40px;height:40px;border-radius:6px;object-fit:cover">
              @else
                <div style="width:40px;height:40px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:var(--text-muted)"><i class="bi bi-box"></i></div>
              @endif
              <div>
                <div class="fw-600 fs-14">{{ $p->name }}</div>
                <div class="text-muted fs-11">{{ $p->unit?->name ?? '—' }}</div>
              </div>
            </div>
          </td>
          <td><code class="fs-12">{{ $p->sku ?? '—' }}</code></td>
          <td>{{ $p->category?->name ?? '—' }}</td>
          <td>
            @if($p->current_stock <= $p->alert_quantity)
              <span class="badge badge-danger">{{ $p->current_stock }} {{ $p->unit?->short_name }}</span>
            @else
              <span class="badge badge-success">{{ $p->current_stock }} {{ $p->unit?->short_name }}</span>
            @endif
          </td>
          <td><span class="badge badge-secondary fs-11">{{ $p->alert_quantity }}</span></td>
          <td><span class="badge {{ $p->is_active ? 'badge-success' : 'badge-warning' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span></td>
          <td>
            <div class="flex gap-8">
              <a href="{{ route('inventory.products.edit', $p) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
              <form method="POST" action="{{ route('inventory.products.destroy', $p) }}" onsubmit="return confirm('Delete product?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">📦</div><h3>No products found</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:16px 20px">{{ $products->links() }}</div>
</div>
@endsection
