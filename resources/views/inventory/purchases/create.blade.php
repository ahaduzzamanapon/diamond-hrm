@extends('layouts.app')
@section('title','New Purchase')
@section('breadcrumb')
  <a href="{{ route('inventory.purchases.index') }}">Purchases</a> &rsaquo; <span class="current">New</span>
@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">New Purchase</h1><p class="page-subtitle">Record incoming stock from suppliers</p></div>
  <a href="{{ route('inventory.purchases.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" action="{{ route('inventory.purchases.store') }}">
  @csrf
  <div class="glass-card mb-20">
    <div class="card-header"><div class="card-title">Purchase Details</div></div>
    <div class="card-body">
      <div class="grid g-3 gap-16">
        <div class="form-group">
          <label class="form-label">Supplier <span class="req">*</span></label>
          <select name="supplier_id" class="form-control" required>
            <option value="">Select Supplier...</option>
            @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Purchase Date <span class="req">*</span></label>
          <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="form-group">
          <label class="form-label">Reference No. / Inv No.</label>
          <input type="text" name="reference_no" class="form-control">
        </div>
      </div>
      <div class="form-group mt-12">
        <label class="form-label">Note / Remarks</label>
        <input type="text" name="note" class="form-control">
      </div>
    </div>
  </div>

  <div class="glass-card mb-20">
    <div class="card-header"><div class="card-title">Items</div></div>
    <div class="table-wrapper">
      <table id="itemsTable">
        <thead>
          <tr>
            <th style="width:40%">Product</th>
            <th style="width:20%">Quantity</th>
            <th style="width:20%">Unit Price</th>
            <th style="width:15%">Subtotal</th>
            <th style="width:5%"></th>
          </tr>
        </thead>
        <tbody id="itemsBody">
          <!-- Dynamic Rows -->
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" class="text-right fw-600" style="text-align:right">Total Amount:</td>
            <td class="fw-700 fs-16" id="grandTotal">0.00</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
    <div class="card-body border-t">
      <button type="button" class="btn btn-sm btn-secondary" onclick="addRow()"><i class="bi bi-plus-lg"></i> Add Item Line</button>
    </div>
  </div>

  <div class="flex gap-8 justify-end">
    <button type="submit" class="btn btn-primary" onclick="return confirm('Save purchase? Items will be pending receipt.')"><i class="bi bi-save"></i> Save Purchase Order</button>
  </div>
</form>

<div style="display:none" id="productOptions">
  <option value="">Select product...</option>
  @foreach($products as $p)<option value="{{ $p->id }}" data-sku="{{ $p->sku }}">{{ $p->name }} {{ $p->sku ? '('.$p->sku.')' : '' }}</option>@endforeach
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
      <input type="number" name="items[${rowCount}][quantity]" class="form-control qty" min="1" value="1" required onchange="calcRow(this)" onkeyup="calcRow(this)">
    </td>
    <td>
      <input type="number" name="items[${rowCount}][unit_price]" class="form-control price" min="0" step="0.01" value="0.00" required onchange="calcRow(this)" onkeyup="calcRow(this)">
    </td>
    <td class="subtotal fw-600">0.00</td>
    <td>
      <button type="button" class="btn btn-sm btn-danger px-8" onclick="removeRow(this)"><i class="bi bi-x"></i></button>
    </td>
  `;
  tbody.appendChild(tr);
  rowCount++;
}

function removeRow(btn) {
  btn.closest('tr').remove();
  calcAll();
}

function calcRow(el) {
  const tr = el.closest('tr');
  const qty = parseFloat(tr.querySelector('.qty').value) || 0;
  const price = parseFloat(tr.querySelector('.price').value) || 0;
  const sub = qty * price;
  tr.querySelector('.subtotal').innerText = sub.toFixed(2);
  calcAll();
}

function calcAll() {
  let total = 0;
  document.querySelectorAll('.subtotal').forEach(el => {
    total += parseFloat(el.innerText) || 0;
  });
  document.getElementById('grandTotal').innerText = total.toFixed(2);
}

// Add init row
window.onload = () => addRow();
</script>
@endpush
@endsection
