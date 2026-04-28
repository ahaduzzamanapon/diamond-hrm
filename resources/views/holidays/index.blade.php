@extends('layouts.app')
@section('title','Holidays')
@section('breadcrumb')<span class="current">Holidays</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Holidays</h1><p class="page-subtitle">Manage public & company holidays for {{ $year }}</p></div>
  <div class="flex gap-8">
    <form method="GET" class="flex gap-8">
      <select name="year" class="form-control" style="width:auto" onchange="this.form.submit()">
        @for($y=2023;$y<=2027;$y++)<option {{ $y==$year?'selected':'' }}>{{ $y }}</option>@endfor
      </select>
    </form>
    <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')"><i class="bi bi-plus-lg"></i> Add Holiday</button>
  </div>
</div>

<div class="glass-card">
  <div class="table-wrapper">
    <table>
      <thead><tr><th>#</th><th>Holiday Name</th><th>Date</th><th>Day</th><th>Type</th><th>Branch</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($holidays as $h)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td><strong>{{ $h->name }}</strong></td>
          <td>{{ \Carbon\Carbon::parse($h->date)->format('d M Y') }}</td>
          <td>{{ \Carbon\Carbon::parse($h->date)->format('l') }}</td>
          <td>
            <span class="badge {{ match($h->type??'public') { 'public'=>'badge-info','national'=>'badge-primary','optional'=>'badge-warning',default=>'badge-secondary'} }}">
              {{ ucfirst($h->type ?? 'Public') }}
            </span>
          </td>
          <td>{{ $h->branch?->name ?? 'All Branches' }}</td>
          <td>
            <form method="POST" action="{{ route('holidays.destroy',$h) }}" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
        @empty
        <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">📅</div><h3>No holidays for {{ $year }}</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="modal-overlay" id="addModal">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Add Holiday</span><button class="modal-close" onclick="document.getElementById('addModal').classList.remove('open')">&times;</button></div>
    <form method="POST" action="{{ route('holidays.store') }}">@csrf
      <div class="modal-body">
        <div class="form-group"><label class="form-label">Holiday Name <span class="req">*</span></label><input name="name" class="form-control" required placeholder="e.g. Eid-ul-Fitr"></div>
        <div class="grid g-2 gap-12">
          <div class="form-group"><label class="form-label">Date <span class="req">*</span></label><input type="date" name="date" class="form-control" required value="{{ date('Y-m-d') }}"></div>
          <div class="form-group"><label class="form-label">Type</label>
            <select name="type" class="form-control">
              <option value="public">Public</option><option value="national">National</option><option value="optional">Optional</option><option value="company">Company</option>
            </select>
          </div>
        </div>
        <div class="form-group"><label class="form-label">Branch (leave blank = all branches)</label>
          <select name="branch_id" class="form-control"><option value="">All Branches</option>@foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select>
        </div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button><button type="submit" class="btn btn-primary">Save Holiday</button></div>
    </form>
  </div>
</div>
@endsection
