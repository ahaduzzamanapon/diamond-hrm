@extends('layouts.app')
@section('title','Review Requisition')
@section('breadcrumb')
  <a href="{{ route('inventory.requisitions.index') }}">Requisitions</a> &rsaquo; <span class="current">Review</span>
@endsection

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Requisition #{{ str_pad($requisition->id, 5, '0', STR_PAD_LEFT) }}</h1>
    <p class="page-subtitle">Requested by: {{ $requisition->employee?->first_name }} {{ $requisition->employee?->last_name }}</p>
  </div>
  <div class="flex gap-8">
    <a href="{{ route('inventory.requisitions.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
  </div>
</div>

<div class="grid g-3 gap-20 mb-20">
  <div class="glass-card p-20">
    <div class="text-muted fs-12 mb-4 text-uppercase fw-600">Status</div>
    <div class="fs-16 fw-600">
      @if($requisition->status === 'pending') <span class="badge badge-warning">Pending Approval</span>
      @elseif($requisition->status === 'approved') <span class="badge badge-primary">Approved (Awaiting Supply)</span>
      @elseif($requisition->status === 'supplied') <span class="badge badge-success">Supplied Completed</span>
      @else <span class="badge badge-danger">Rejected</span>
      @endif
    </div>
  </div>
  <div class="glass-card p-20">
    <div class="text-muted fs-12 mb-4 text-uppercase fw-600">Request Date</div>
    <div class="fs-16 fw-600">{{ $requisition->request_date->format('d M, Y') }}</div>
  </div>
  <div class="glass-card p-20">
    <div class="text-muted fs-12 mb-4 text-uppercase fw-600">Department</div>
    <div class="fs-16 fw-600">{{ $requisition->department?->name ?? '—' }}</div>
  </div>
</div>

@if($requisition->note)
<div class="glass-card p-20 mb-20">
  <div class="fw-600 mb-8">Reason / Note</div>
  <p class="mb-0 text-muted">{{ $requisition->note }}</p>
</div>
@endif

{{-- 1. APPROVAL WORKFLOW --}}
@if($requisition->status === 'pending')
  <div class="glass-card p-20 mb-20 flex gap-12" style="background:rgba(255,200,0,0.05); border:1px solid rgba(255,200,0,0.2)">
    <div class="flex-grow">
      <div class="fw-600 fs-16 mb-4 text-warning">Approval Required</div>
      <div class="text-muted fs-13">This requisition needs approval before items can be supplied from the inventory.</div>
    </div>
    <div class="flex gap-8 items-center">
      <form method="POST" action="{{ route('inventory.requisitions.reject', $requisition) }}" onsubmit="return confirm('Are you sure you want to REJECT this request?')">
        @csrf
        <button class="btn btn-outline-danger px-16">Reject</button>
      </form>
      <form method="POST" action="{{ route('inventory.requisitions.approve', $requisition) }}" onsubmit="return confirm('Are you sure you want to APPROVE this request?')">
        @csrf
        <button class="btn btn-primary px-16"><i class="bi bi-check-lg"></i> Approve</button>
      </form>
    </div>
  </div>
@endif

{{-- 2. SUPPLY WORKFLOW (Only if Approved) --}}
@if($requisition->status === 'approved')
<form method="POST" action="{{ route('inventory.requisitions.supply', $requisition) }}">
  @csrf
  <div class="glass-card mb-20" style="border-left:4px solid var(--primary)">
    <div class="card-header">
      <div class="card-title">Fulfill / Supply Items</div>
      <div class="fs-12 text-muted fw-normal">Specify how many items you are supplying from current stock.</div>
    </div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Requested Qty</th>
            <th>Current Stock Available</th>
            <th>Qty to Supply</th>
          </tr>
        </thead>
        <tbody>
          @foreach($requisition->items as $item)
          <tr>
            <td class="fw-600">{{ $item->product->name ?? 'Unknown' }}</td>
            <td class="fw-600 text-primary">{{ $item->qty_requested }} {{ $item->product->unit?->short_name }}</td>
            <td>
              <span class="badge {{ $item->product->current_stock >= $item->qty_requested ? 'badge-success' : 'badge-danger' }}">
                {{ $item->product->current_stock }} {{ $item->product->unit?->short_name }}
              </span>
            </td>
            <td style="width:200px">
              <input type="number" name="items[{{ $item->id }}]" class="form-control" value="{{ min($item->qty_requested, $item->product->current_stock) }}" min="0" max="{{ $item->product->current_stock }}">
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-body border-t flex justify-end">
      <button type="submit" class="btn btn-success" onclick="return confirm('Mark as supplied? This will irrevocably deduct items from inventory stock.')">
        <i class="bi bi-box-seam"></i> Confirm & Deduct Stock
      </button>
    </div>
  </div>
</form>
@endif

{{-- 3. FINAL VIEW (Supplied or Rejected) --}}
@if(in_array($requisition->status, ['supplied', 'rejected']))
<div class="glass-card mb-20">
  <div class="card-header"><div class="card-title">Requested Items Log</div></div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>Requested Qty</th>
          <th>Supplied Qty</th>
        </tr>
      </thead>
      <tbody>
        @foreach($requisition->items as $item)
        <tr>
          <td class="fw-600">{{ $item->product->name ?? 'Unknown' }}</td>
          <td>{{ $item->qty_requested }} {{ $item->product->unit?->short_name }}</td>
          <td>
            @if($requisition->status === 'supplied')
              <span class="text-success fw-600">{{ $item->qty_supplied }} {{ $item->product->unit?->short_name }}</span>
            @else
              <span class="text-muted">0 (Rejected)</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

@endsection
