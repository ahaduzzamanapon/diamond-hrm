@extends('layouts.app')
@section('title','View Purchase')
@section('breadcrumb')
  <a href="{{ route('inventory.purchases.index') }}">Purchases</a> &rsaquo; <span class="current">View</span>
@endsection

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Purchase #{{ str_pad($purchase->id, 5, '0', STR_PAD_LEFT) }}</h1>
    <p class="page-subtitle">Supplier: {{ $purchase->supplier->name ?? '—' }}</p>
  </div>
  <div class="flex gap-8">
    @if($purchase->status === 'pending')
      <form method="POST" action="{{ route('inventory.purchases.receive', $purchase) }}" onsubmit="return confirm('Mark as received? This will increment stock for all items.')">
        @csrf
        <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle"></i> Mark Received</button>
      </form>
    @endif
    <a href="{{ route('inventory.purchases.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
  </div>
</div>

<div class="grid g-3 gap-20 mb-20">
  <div class="glass-card p-20">
    <div class="text-muted fs-12 mb-4 text-uppercase fw-600">Status</div>
    <div class="fs-16 fw-600">
      @if($purchase->status === 'received') <span class="text-success"><i class="bi bi-check-circle-fill"></i> Received</span>
      @else <span class="text-warning"><i class="bi bi-clock-fill"></i> Pending</span> @endif
    </div>
  </div>
  <div class="glass-card p-20">
    <div class="text-muted fs-12 mb-4 text-uppercase fw-600">Purchase Date</div>
    <div class="fs-16 fw-600">{{ $purchase->purchase_date->format('d M, Y') }}</div>
  </div>
  <div class="glass-card p-20">
    <div class="text-muted fs-12 mb-4 text-uppercase fw-600">Total Amount</div>
    <div class="fs-16 fw-600">BDT {{ number_format($purchase->total_amount, 2) }}</div>
  </div>
</div>

<div class="glass-card mb-20">
  <div class="card-header"><div class="card-title">Order Items</div></div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>SKU</th>
          <th>Quantity</th>
          <th>Unit Price</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @foreach($purchase->items as $item)
        <tr>
          <td class="fw-600">{{ $item->product->name ?? 'Unknown' }}</td>
          <td><code class="fs-12">{{ $item->product->sku ?? '—' }}</code></td>
          <td>{{ $item->quantity }} {{ $item->product->unit?->short_name }}</td>
          <td>BDT {{ number_format($item->unit_price, 2) }}</td>
          <td class="fw-600">BDT {{ number_format($item->subtotal, 2) }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="text-right fw-600" style="text-align:right">Total:</td>
          <td class="fw-700 fs-15">BDT {{ number_format($purchase->total_amount, 2) }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

@if($purchase->note)
<div class="glass-card p-20">
  <div class="fw-600 mb-8">Note / Remarks</div>
  <p class="mb-0 text-muted">{{ $purchase->note }}</p>
</div>
@endif

@endsection
