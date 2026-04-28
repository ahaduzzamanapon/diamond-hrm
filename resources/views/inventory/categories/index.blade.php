@extends('layouts.app')
@section('title','Product Categories')
@section('breadcrumb')<a href="#">Inventory</a> &rsaquo; <span class="current">Categories</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Product Categories</h1><p class="page-subtitle">Manage inventory categories</p></div>
</div>

<div class="flex gap-8 mb-20">
  <a href="{{ route('inventory.categories.index') }}" class="btn btn-primary">Categories</a>
  <a href="{{ route('inventory.units.index') }}" class="btn btn-secondary">Units</a>
</div>

<div class="glass-card">
  <div class="card-header">
    <div class="card-title">All Categories</div>
    <button class="btn btn-sm btn-primary" onclick="document.getElementById('addModal').classList.add('open')"><i class="bi bi-plus"></i> Add Category</button>
  </div>
  <div class="table-wrapper">
    <table>
      <thead><tr><th>Name</th><th>Description</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @foreach($categories as $c)
        <tr>
          <td class="fw-600">{{ $c->name }}</td>
          <td class="text-muted fs-13">{{ $c->description ?? '—' }}</td>
          <td><span class="badge badge-info">{{ $c->products_count }}</span></td>
          <td><span class="badge {{ $c->is_active ? 'badge-success' : 'badge-danger' }}">{{ $c->is_active ? 'Active' : 'Inactive' }}</span></td>
          <td>
            <form method="POST" action="{{ route('inventory.categories.destroy', $c) }}" style="display:inline" onsubmit="return confirm('Delete category?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- Add Modal --}}
<div class="modal-overlay" id="addModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">Add Category</h3>
      <button class="modal-close" onclick="document.getElementById('addModal').classList.remove('open')">&times;</button>
    </div>
    <form method="POST" action="{{ route('inventory.categories.store') }}">
      @csrf
      <div class="modal-body">
        <div class="form-group mb-12">
          <label class="form-label">Name <span class="req">*</span></label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group border-0">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Category</button>
      </div>
    </form>
  </div>
</div>
@endsection
