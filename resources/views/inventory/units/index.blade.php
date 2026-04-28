@extends('layouts.app')
@section('title','Inventory Units')
@section('breadcrumb')<a href="#">Inventory</a> &rsaquo; <span class="current">Units</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Inventory Units</h1><p class="page-subtitle">Manage measurement units</p></div>
</div>

<div class="flex gap-8 mb-20">
  <a href="{{ route('inventory.categories.index') }}" class="btn btn-secondary">Categories</a>
  <a href="{{ route('inventory.units.index') }}" class="btn btn-primary">Units</a>
</div>

<div class="glass-card">
  <div class="card-header">
    <div class="card-title">All Units</div>
    <button class="btn btn-sm btn-primary" onclick="document.getElementById('addModal').classList.add('open')"><i class="bi bi-plus"></i> Add Unit</button>
  </div>
  <div class="table-wrapper">
    <table>
      <thead><tr><th>Name</th><th>Short Name</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @foreach($units as $u)
        <tr>
          <td class="fw-600">{{ $u->name }}</td>
          <td><code class="fs-13">{{ $u->short_name ?? '—' }}</code></td>
          <td><span class="badge badge-info">{{ $u->products_count }}</span></td>
          <td><span class="badge {{ $u->is_active ? 'badge-success' : 'badge-danger' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</span></td>
          <td>
            <form method="POST" action="{{ route('inventory.units.destroy', $u) }}" style="display:inline" onsubmit="return confirm('Delete unit?')">
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
      <h3 class="modal-title">Add Unit</h3>
      <button class="modal-close" onclick="document.getElementById('addModal').classList.remove('open')">&times;</button>
    </div>
    <form method="POST" action="{{ route('inventory.units.store') }}">
      @csrf
      <div class="modal-body">
        <div class="form-group mb-12">
          <label class="form-label">Name <span class="req">*</span></label>
          <input type="text" name="name" class="form-control" required placeholder="e.g. Piece, Box, Kilogram">
        </div>
        <div class="form-group border-0">
          <label class="form-label">Short Name</label>
          <input type="text" name="short_name" class="form-control" placeholder="e.g. Psc, Box, Kg">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Unit</button>
      </div>
    </form>
  </div>
</div>
@endsection
