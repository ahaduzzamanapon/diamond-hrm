@extends('layouts.app')
@section('title','Purchases')
@section('breadcrumb')<a href="#">Inventory</a> &rsaquo; <span class="current">Purchases</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Purchases</h1><p class="page-subtitle">Manage incoming inventory orders</p></div>
  <a href="{{ route('inventory.purchases.create') }}" class="btn btn-primary"><i class="bi bi-cart-plus"></i> New Purchase</a>
</div>

<div class="filter-bar">
  <form method="GET" class="flex gap-8 flex-wrap" style="width:100%">
    <div class="form-group" style="flex:1;min-width:150px">
      <label class="form-label">Supplier</label>
      <select name="supplier_id" class="form-control">
        <option value="">All Suppliers</option>
        @foreach($suppliers as $s)
          <option value="{{ $s->id }}" {{ request('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-group" style="flex:1;min-width:150px">
      <label class="form-label">Status</label>
      <select name="status" class="form-control">
        <option value="">All Statuses</option>
        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending Receipt</option>
        <option value="received" {{ request('status')=='received'?'selected':'' }}>Received</option>
      </select>
    </div>
    <div class="form-group" style="display:flex;align-items:flex-end;gap:8px;min-width:auto">
      <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
      <a href="{{ route('inventory.purchases.index') }}" class="btn btn-secondary"><i class="bi bi-x-lg"></i></a>
    </div>
  </form>
</div>

<div class="glass-card">
  <div class="table-wrapper">
    <table>
      <thead><tr><th>Purchase Date</th><th>Supplier</th><th>Reference</th><th>Total Amount</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($purchases as $p)
        <tr>
          <td class="fw-600">{{ $p->purchase_date->format('d M, Y') }}</td>
          <td>{{ $p->supplier?->name ?? '—' }}</td>
          <td><code class="fs-12">{{ $p->reference_no ?? '—' }}</code></td>
          <td class="fw-600">BDT {{ number_format($p->total_amount, 2) }}</td>
          <td>
            @if($p->status === 'received')
              <span class="badge badge-success">Received</span>
            @else
              <span class="badge badge-warning">Pending</span>
            @endif
          </td>
          <td>
            <div class="flex gap-8">
              <a href="{{ route('inventory.purchases.show', $p) }}" class="btn btn-sm btn-secondary"><i class="bi bi-eye"></i> View</a>
              @if($p->status === 'pending')
                <form method="POST" action="{{ route('inventory.purchases.receive', $p) }}" onsubmit="return confirm('Mark as received? This will update product stock.')">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check2-circle"></i> Receive</button>
                </form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6"><div class="empty-state"><h3>No purchases found</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:16px 20px">{{ $purchases->links() }}</div>
</div>
@endsection
