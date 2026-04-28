@extends('layouts.app')
@section('title','Notice Board')
@section('breadcrumb')<span class="current">Notice Board</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Notice Board</h1><p class="page-subtitle">Company notices, announcements, and events</p></div>
  @if(Auth::user()->hasRole(['super-admin','hr-admin','hr','branch-manager']))
  <a href="{{ route('notices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Post Notice</a>
  @endif
</div>

<div class="glass-card">
  <div class="table-wrapper">
    <table>
      <thead><tr><th>Title</th><th>Type</th><th>Audience</th><th>Branch</th><th>Published</th><th>Expires</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($notices as $notice)
        <tr>
          <td>
            <div class="fw-600">{{ $notice->title }}</div>
            <div class="text-muted fs-13">{{ Str::limit(strip_tags($notice->body), 60) }}</div>
          </td>
          <td>
            <span class="badge {{ match($notice->type??'general'){'urgent'=>'badge-danger','event'=>'badge-info','policy'=>'badge-warning',default=>'badge-secondary'} }}">
              {{ ucfirst($notice->type ?? 'General') }}
            </span>
          </td>
          <td class="fs-13">{{ ucfirst($notice->audience ?? 'all') }}</td>
          <td class="fs-13">{{ $notice->branch?->name ?? 'All Branches' }}</td>
          <td class="fs-13 text-muted">{{ $notice->published_at ? \Carbon\Carbon::parse($notice->published_at)->format('d M Y') : '—' }}</td>
          <td class="fs-13 text-muted">{{ $notice->expires_at ? \Carbon\Carbon::parse($notice->expires_at)->format('d M Y') : '∞' }}</td>
          <td>
            <span class="badge {{ $notice->is_published ? 'badge-success' : 'badge-secondary' }}">
              {{ $notice->is_published ? 'Published' : 'Draft' }}
            </span>
          </td>
          <td>
            <div class="flex gap-8">
              <a href="{{ route('notices.edit', $notice) }}" class="btn btn-sm btn-secondary"><i class="bi bi-pencil"></i></a>
              <form method="POST" action="{{ route('notices.destroy', $notice) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📢</div><h3>No notices posted yet</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:14px 18px">{{ $notices->links() }}</div>
</div>
@endsection
