@extends('layouts.app')
@section('title','New Requisition')
@section('breadcrumb')
  <a href="{{ route('inventory.requisitions.index') }}">Requisitions</a> &rsaquo; <span class="current">Apply</span>
@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">New Requisition</h1><p class="page-subtitle">Request inventory items or supplies</p></div>
  <a href="{{ route('inventory.requisitions.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" action="{{ route('inventory.requisitions.store') }}">
  @csrf
  <div class="glass-card mb-20">
    <div class="card-header"><div class="card-title">Request Details</div></div>
    <div class="card-body">
      <div class="grid g-3 gap-16">
        <div class="form-group">
          <label class="form-label">Employee ID</label>
          <input type="text" class="form-control" value="{{ Auth::user()->employee?->employee_id ?? 'N/A' }}" disabled>
        </div>
        <div class="form-group">
          <label class="form-label">Name</label>
          <input type="text" class="form-control" value="{{ Auth::user()->name }}" disabled>
        </div>
        <div class="form-group">
          <label class="form-label">Request Date <span class="req">*</span></label>
          <input type="date" name="request_date" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>
      </div>
      <div class="form-group mt-16">
        <label class="form-label">Reason / Note</label>
        <textarea name="note" class="form-control" rows="2" placeholder="Why are these items needed?"></textarea>
      </div>
    </div>
  </div>

  <div class="glass-card mb-20">
    <div class="card-header"><div class="card-title">Requested Items</div></div>
    <div class="table-wrapper">
      <table id="itemsTable">
        <thead>
          <tr>
            <th style="width:50%">Product</th>
            <th style="width:30%">Quantity Needed</th>
            <th style="width:20%"></th>
          </tr>
        </thead>
        <tbody id="itemsBody">
          <!-- Dynamic Rows -->
        </tbody>
      </table>
    </div>
    <div class="card-body border-t">
      <button type="button" class="btn btn-sm btn-secondary" onclick="addRow()"><i class="bi bi-plus-lg"></i> Add Item Line</button>
    </div>
  </div>

  <div class="flex gap-8 justify-end">
    <button type="submit" class="btn btn-primary" onclick="return confirm('Submit requisition for approval?')"><i class="bi bi-send"></i> Submit Request</button>
  </div>
</form>

<div style="display:none" id="productOptions">
  <option value="">Select product...</option>
  @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }} (In Stock: {{ $p->current_stock }})</option>@endforeach
</div>

@push('scripts')
<script>
let rowCount = 0;
function addRow() {
  const tbody = document.getElementById('itemsBody');
  const tr = document.createElement('tr');
  const options = document.getElementById('productOptions').innerHTML;
  
  tr.innerHTML = `
    <td>
      <select name="items[${rowCount}][product_id]" class="form-control" required>${options}</select>
    </td>
    <td>
      <input type="number" name="items[${rowCount}][quantity]" class="form-control" min="1" value="1" required>
    </td>
    <td>
      <button type="button" class="btn btn-sm btn-danger px-8" onclick="this.closest('tr').remove()"><i class="bi bi-x"></i></button>
    </td>
  `;
  tbody.appendChild(tr);
  rowCount++;
}

// Add init row
window.onload = () => addRow();
</script>
@endpush
@endsection
