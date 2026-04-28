@extends('layouts.app')
@section('title','Suppliers')
@section('breadcrumb')<a href="#">Inventory</a> &rsaquo; <span class="current">Suppliers</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Suppliers</h1><p class="page-subtitle">Manage inventory vendors</p></div>
  <a href="{{ route('inventory.suppliers.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Add Supplier</a>
</div>

<div class="glass-card">
  <div class="table-wrapper">
    <table>
      <thead><tr><th>Name</th><th>Contact Person</th><th>Phone</th><th>Purchases</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @foreach($suppliers as $s)
        <tr>
          <td class="fw-600">{{ $s->name }}</td>
          <td class="text-muted fs-13">{{ $s->contact_person ?? '—' }}</td>
          <td>{{ $s->phone ?? '—' }}</td>
          <td><span class="badge badge-secondary">{{ $s->purchases_count }}</span></td>
          <td><span class="badge {{ $s->is_active ? 'badge-success' : 'badge-danger' }}">{{ $s->is_active ? 'Active' : 'Inactive' }}</span></td>
          <td>
            <div class="flex gap-8">
              <a href="{{ route('inventory.suppliers.edit', $s) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
              <form method="POST" action="{{ route('inventory.suppliers.destroy', $s) }}" onsubmit="return confirm('Delete supplier?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div style="padding:16px 20px">{{ $suppliers->links() }}</div>
</div>
@endsection
