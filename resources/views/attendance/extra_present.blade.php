@extends('layouts.app')
@section('title','Extra Present Requests')
@section('breadcrumb')<a href="{{ route('attendance.index') }}">Attendance</a><span class="sep">/</span><span class="current">Extra Present</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Extra Present Requests</h1><p class="page-subtitle">Employees who worked on holidays or weekends</p></div>
</div>

@if(session('success'))
<div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

<div class="glass-card">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr><th>Employee</th><th>Date</th><th>Day Type</th><th>Holiday</th><th>Extra Pay</th><th>BM Status</th><th>HR Status</th><th>Requested</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($requests as $req)
        <tr>
          <td>
            <div class="flex gap-8" style="align-items:center">
              <img src="{{ $req->employee?->photo_url ?? 'https://ui-avatars.com/api/?name=Unknown&background=6366f1&color=fff&size=128' }}" class="avatar avatar-sm">
              <div>
                <div class="fw-600 fs-13">{{ $req->employee?->name ?? '(Deleted Employee)' }}</div>
                <div class="text-muted" style="font-size:11px">{{ $req->employee?->branch?->name ?? '—' }}</div>
              </div>
            </div>
          </td>
          <td class="fs-13">{{ \Carbon\Carbon::parse($req->date)->format('d M Y') }}</td>
          <td><span class="badge {{ $req->day_type==='holiday'?'badge-warning':'badge-info' }}">{{ ucfirst($req->day_type) }}</span></td>
          <td class="fs-13 text-muted">{{ $req->holiday_name ?? '—' }}</td>
          <td><strong>৳{{ number_format($req->extra_pay,2) }}</strong></td>
          <td>
            <span class="badge {{ match($req->bm_status??'pending'){'approved'=>'badge-success','rejected'=>'badge-danger',default=>'badge-warning'} }}">
              {{ ucfirst($req->bm_status ?? 'Pending') }}
            </span>
          </td>
          <td>
            <span class="badge {{ match($req->hr_status??'pending'){'approved'=>'badge-success','rejected'=>'badge-danger',default=>'badge-warning'} }}">
              {{ ucfirst($req->hr_status ?? 'Pending') }}
            </span>
          </td>
          <td class="text-muted fs-13">{{ $req->created_at->format('d M Y') }}</td>
          <td>
            @if(($req->bm_status ?? 'pending') === 'pending' && auth()->user()->hasRole(['branch-manager','hr-admin','super-admin']))
            <div class="flex gap-8">
              <form method="POST" action="{{ route('attendance.extra.approve', $req) }}">@csrf
                <input type="hidden" name="level" value="bm"><input type="hidden" name="action" value="approve">
                <button class="btn btn-sm btn-success" title="BM Approve"><i class="bi bi-check-lg"></i> BM</button>
              </form>
              <form method="POST" action="{{ route('attendance.extra.approve', $req) }}">@csrf
                <input type="hidden" name="level" value="bm"><input type="hidden" name="action" value="reject">
                <button class="btn btn-sm btn-danger"><i class="bi bi-x-lg"></i></button>
              </form>
            </div>
            @elseif(($req->hr_status ?? 'pending') === 'pending' && auth()->user()->hasRole(['hr-admin','super-admin']))
            <div class="flex gap-8">
              <form method="POST" action="{{ route('attendance.extra.approve', $req) }}">@csrf
                <input type="hidden" name="level" value="hr"><input type="hidden" name="action" value="approve">
                <button class="btn btn-sm btn-success" title="HR Approve"><i class="bi bi-check-lg"></i> HR</button>
              </form>
              <form method="POST" action="{{ route('attendance.extra.approve', $req) }}">@csrf
                <input type="hidden" name="level" value="hr"><input type="hidden" name="action" value="reject">
                <button class="btn btn-sm btn-danger"><i class="bi bi-x-lg"></i></button>
              </form>
            </div>
            @else
            <span class="text-muted fs-13">—</span>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">⭐</div><h3>No extra present requests</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:14px 18px">{{ $requests->links() }}</div>
</div>
@endsection
