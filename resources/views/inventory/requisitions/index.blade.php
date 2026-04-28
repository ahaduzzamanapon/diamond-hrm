@extends('layouts.app')
@section('title','Requisitions')
@section('breadcrumb')<a href="#">Inventory</a> &rsaquo; <span class="current">Requisitions</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Requisitions</h1><p class="page-subtitle">Manage internal requests for items/supplies</p></div>
  <a href="{{ route('inventory.requisitions.create') }}" class="btn btn-primary"><i class="bi bi-clipboard-plus"></i> New Requisition</a>
</div>

<div class="filter-bar">
  <form method="GET" class="flex gap-8 flex-wrap" style="width:100%">
    <div class="form-group" style="flex:1;min-width:150px">
      <label class="form-label">Status</label>
      <select name="status" class="form-control">
        <option value="">All Statuses</option>
        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending Approval</option>
        <option value="approved" {{ request('status')=='approved'?'selected':'' }}>Approved (Awaiting Supply)</option>
        <option value="supplied" {{ request('status')=='supplied'?'selected':'' }}>Supplied</option>
        <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Rejected</option>
      </select>
    </div>
    <div class="form-group" style="display:flex;align-items:flex-end;gap:8px;min-width:auto">
      <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
      <a href="{{ route('inventory.requisitions.index') }}" class="btn btn-secondary"><i class="bi bi-x-lg"></i></a>
    </div>
  </form>
</div>

<div class="glass-card">
  <div class="table-wrapper">
    <table>
      <thead><tr><th>Date</th><th>Employee</th><th>Department</th><th>Items</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($requisitions as $r)
        <tr>
          <td class="fw-600">{{ $r->request_date->format('d M, Y') }}</td>
          <td>
            <div class="fw-600">{{ $r->employee?->first_name }} {{ $r->employee?->last_name }}</div>
            <div class="text-muted fs-11">{{ $r->employee?->employee_id }}</div>
          </td>
          <td>{{ $r->department?->name ?? '—' }}</td>
          <td><span class="badge badge-info">{{ $r->items->count() }} line(s)</span></td>
          <td>
            @if($r->status === 'pending') <span class="badge badge-warning">Pending</span>
            @elseif($r->status === 'approved') <span class="badge badge-primary">Approved</span>
            @elseif($r->status === 'supplied') <span class="badge badge-success">Supplied</span>
            @else <span class="badge badge-danger">Rejected</span>
            @endif
          </td>
          <td>
            <a href="{{ route('inventory.requisitions.show', $r) }}" class="btn btn-sm btn-secondary"><i class="bi bi-view-list"></i> Review</a>
          </td>
        </tr>
        @empty
        <tr><td colspan="6"><div class="empty-state"><h3>No requisitions found</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:16px 20px">{{ $requisitions->links() }}</div>
</div>
@endsection
