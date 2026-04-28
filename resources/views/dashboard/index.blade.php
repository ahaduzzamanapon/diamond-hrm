@extends('layouts.app')
@section('title','Dashboard')
@section('breadcrumb')<span class="current">Dashboard</span>@endsection

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ Str::words(Auth::user()->name, 1, '') }}! 👋</h1>
    <p class="page-subtitle">{{ $today->format('l, F j Y') }} — Here's what's happening today.</p>
  </div>
  <div class="flex gap-8">
    <a href="{{ route('attendance.manual') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Manual Attendance</a>
  </div>
</div>

{{-- ── KPI Stats Row ──────────────────────────────────────────────────────── --}}
@can('view_employees')
<div class="grid g-4 mb-24">
  <div class="stat-card" style="color:var(--accent)">
    <div class="stat-icon" style="background:rgba(99,102,241,0.12)">👥</div>
    <div class="stat-value">{{ number_format($totalEmployees) }}</div>
    <div class="stat-label">Total Employees</div>
    <div class="stat-change up"><i class="bi bi-arrow-up"></i> {{ $newThisMonth }} new this month</div>
  </div>

  <div class="stat-card" style="color:var(--success)">
    <div class="stat-icon" style="background:rgba(16,185,129,0.12)">✅</div>
    <div class="stat-value">{{ $presentToday }}</div>
    <div class="stat-label">Present Today</div>
    <div class="stat-change {{ $totalEmployees > 0 && ($presentToday/$totalEmployees) > 0.8 ? 'up' : 'down' }}">
      <i class="bi bi-arrow-{{ $totalEmployees > 0 && ($presentToday/$totalEmployees) > 0.8 ? 'up' : 'down' }}"></i>
      {{ $totalEmployees > 0 ? round($presentToday/$totalEmployees*100) : 0 }}% attendance rate
    </div>
  </div>

  <div class="stat-card" style="color:var(--danger)">
    <div class="stat-icon" style="background:rgba(239,68,68,0.12)">❌</div>
    <div class="stat-value">{{ $absentToday }}</div>
    <div class="stat-label">Absent Today</div>
    <div class="stat-change">
      <i class="bi bi-clock"></i> {{ $lateToday }} late arrivals
    </div>
  </div>

  <div class="stat-card" style="color:var(--warning)">
    <div class="stat-icon" style="background:rgba(245,158,11,0.12)">🌴</div>
    <div class="stat-value">{{ $onLeaveToday }}</div>
    <div class="stat-label">On Leave Today</div>
    <div class="stat-change">
      <i class="bi bi-hourglass-split"></i> {{ $pendingLeaves }} leave(s) pending
    </div>
  </div>
</div>
@endcan

{{-- ── Staff Personal KPI Row ────────────────────────────────────────────── --}}
@cannot('view_employees')
<div class="grid g-4 mb-24">
  <div class="stat-card" style="color:var(--success)">
    <div class="stat-icon" style="background:rgba(16,185,129,0.12)">✅</div>
    <div class="stat-value">{{ $myPresents ?? 0 }}</div>
    <div class="stat-label">My Presents</div>
    <div class="stat-change"><i class="bi bi-calendar-check"></i> This month</div>
  </div>

  <div class="stat-card" style="color:var(--danger)">
    <div class="stat-icon" style="background:rgba(239,68,68,0.12)">❌</div>
    <div class="stat-value">{{ $myAbsents ?? 0 }}</div>
    <div class="stat-label">My Absents</div>
    <div class="stat-change"><i class="bi bi-calendar-x"></i> This month</div>
  </div>

  <div class="stat-card" style="color:var(--warning)">
    <div class="stat-icon" style="background:rgba(245,158,11,0.12)">🌴</div>
    <div class="stat-value">{{ $myLeavesTaken ?? 0 }}</div>
    <div class="stat-label">Leaves Taken</div>
    <div class="stat-change"><i class="bi bi-sun"></i> This year</div>
  </div>

  <div class="stat-card" style="color:var(--info)">
    <div class="stat-icon" style="background:rgba(59,130,246,0.12)">⏳</div>
    <div class="stat-value">{{ $myPendingLeavesCount ?? 0 }}</div>
    <div class="stat-label">Pending Approval</div>
    <div class="stat-change"><i class="bi bi-hourglass-split"></i> Awaiting review</div>
  </div>
</div>
@endcannot

{{-- ── Row 2: Charts + Pending --}}
{{-- ── Row 2: Charts + Pending --}}
<div class="grid mb-24" style="grid-template-columns:2fr 1fr;gap:20px">
  {{-- Attendance Chart --}}
  @can('view_employees')
  <div class="glass-card">
    <div class="card-header">
      <div class="card-title"><i class="bi bi-bar-chart-fill" style="color:var(--accent)"></i> Attendance (Last 7 Days)</div>
      <a href="{{ route('attendance.monthly') }}" class="btn btn-sm btn-secondary">Full Report</a>
    </div>
    <div class="card-body">
      <canvas id="attChart" height="220"></canvas>
    </div>
  </div>
  @endcan

  {{-- Quick Stats --}}
  <div class="glass-card">
    <div class="card-header">
      <div class="card-title"><i class="bi bi-lightning-fill" style="color:var(--warning)"></i> Quick Actions</div>
    </div>
    <div class="card-body" style="padding:16px;">
      <div style="display:flex;flex-direction:column;gap:10px;">
        @can('manage_leaves')
        <a href="{{ route('leaves.index') }}" class="btn btn-warning" style="justify-content:space-between">
          <span><i class="bi bi-calendar-check"></i> Pending Leaves</span>
          <span class="badge" style="background:rgba(0,0,0,0.2);color:#fff">{{ $pendingLeaves }}</span>
        </a>
        @endcan
        @can('manage_attendance')
        <a href="{{ route('attendance.extra') }}" class="btn btn-success" style="justify-content:space-between">
          <span><i class="bi bi-star-fill"></i> Extra Present</span>
          <span class="badge" style="background:rgba(0,0,0,0.2);color:#fff">{{ $pendingExtra }}</span>
        </a>
        @endcan

        @can('manage_employees')
        <a href="{{ route('employees.create') }}" class="btn btn-secondary">
          <i class="bi bi-person-plus"></i> Add New Employee
        </a>
        @endcan
        @can('manage_attendance')
        <a href="{{ route('attendance.manual') }}" class="btn btn-secondary">
          <i class="bi bi-pencil-square"></i> Manual Attendance
        </a>
        @endcan
        @can('manage_biometric')
        <a href="{{ route('biometric.devices') }}" class="btn btn-secondary">
          <i class="bi bi-fingerprint"></i> Biometric Devices
        </a>
        @endcan
      </div>
    </div>
  </div>
  @can('view_employees')
    @php /* If user can't see the chart on the left, but can see quick stats? Actually, grid columns needs to be fixed if 1 is missing, but keeping it simple for now */ @endphp
  @endcan
</div>

{{-- ── Row 3: Dept Chart + Notices + Birthdays --}}
<div class="grid mb-24" style="grid-template-columns:1fr 1fr 1fr;gap:20px">
  {{-- Department Distribution --}}
  @can('view_all_branches')
  <div class="glass-card">
    <div class="card-header">
      <div class="card-title"><i class="bi bi-pie-chart-fill" style="color:var(--info)"></i> Dept. Distribution</div>
    </div>
    <div class="card-body" style="padding:12px">
      <canvas id="deptChart" height="220"></canvas>
    </div>
  </div>

  {{-- Branches --}}
  <div class="glass-card">
    <div class="card-header">
      <div class="card-title"><i class="bi bi-geo-alt-fill" style="color:var(--success)"></i> Branches</div>
      <a href="{{ route('branches.index') }}" class="btn btn-sm btn-secondary">View All</a>
    </div>
    <div class="card-body" style="padding:12px">
      @forelse($branches as $branch)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px;border-radius:8px;margin-bottom:8px;background:rgba(0,0,0,0.03);transition:all 0.2s"
             onmouseenter="this.style.background='rgba(99,102,241,0.06)'" onmouseleave="this.style.background='rgba(0,0,0,0.03)'">
          <div>
            <div style="font-weight:600;font-size:13px">{{ $branch->name }}</div>
            <div style="font-size:11px;color:var(--text-muted)">{{ $branch->code }}</div>
          </div>
          <span class="badge badge-primary">{{ $branch->employees_count }} emp</span>
        </div>
      @empty
        <div class="empty-state"><div class="empty-icon">🏢</div><p>No branches yet</p></div>
      @endforelse
    </div>
  </div>
  @endcan

  {{-- Birthdays --}}
  <div class="glass-card">
    <div class="card-header">
      <div class="card-title">🎂 Upcoming Birthdays</div>
    </div>
    <div class="card-body" style="padding:12px">
      @forelse($birthdays as $emp)
        <div style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:8px;margin-bottom:8px;background:rgba(0,0,0,0.03)">
          <img src="{{ $emp->photo_url }}" class="avatar avatar-sm" alt="">
          <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $emp->name }}</div>
            <div style="font-size:11px;color:var(--text-muted)">{{ $emp->date_of_birth->copy()->setYear(now()->year)->format('M d') }}</div>
          </div>
          <span style="font-size:18px">🎉</span>
        </div>
      @empty
        <div class="empty-state"><div class="empty-icon">🎊</div><p>No birthdays in next 7 days</p></div>
      @endforelse
    </div>
  </div>
</div>

{{-- ── Notices --}}
<div class="glass-card mb-24">
  <div class="card-header">
    <div class="card-title"><i class="bi bi-megaphone-fill" style="color:var(--warning)"></i> Latest Notices</div>
    <a href="{{ route('notices.index') }}" class="btn btn-sm btn-secondary">All Notices</a>
  </div>
  <div class="table-wrapper">
    <table>
      <thead><tr><th>Title</th><th>Type</th><th>Branch</th><th>Published</th><th>Expires</th></tr></thead>
      <tbody>
        @forelse($notices as $notice)
          <tr>
            <td><strong>{{ $notice->title }}</strong></td>
            <td>
              <span class="badge {{ match($notice->type) { 'urgent'=>'badge-danger','event'=>'badge-info','policy'=>'badge-warning',default=>'badge-secondary'} }}">
                {{ ucfirst($notice->type) }}
              </span>
            </td>
            <td>{{ $notice->branch?->name ?? 'All Branches' }}</td>
            <td>{{ $notice->published_at?->format('d M Y') ?? '—' }}</td>
            <td>{{ $notice->expires_at?->format('d M Y') ?? '∞' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted)">No notices yet</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection

@push('scripts')
<script>
// Attendance Bar Chart
const attCtx = document.getElementById('attChart').getContext('2d');
new Chart(attCtx, {
  type: 'bar',
  data: {
    labels: {!! json_encode($chartLabels) !!},
    datasets: [
      { label: 'Present', data: {!! json_encode($chartPresent) !!}, backgroundColor: 'rgba(16,185,129,0.7)', borderRadius: 6 },
      { label: 'Absent',  data: {!! json_encode($chartAbsent)  !!}, backgroundColor: 'rgba(239,68,68,0.5)',  borderRadius: 6 },
    ]
  },
  options: {
    responsive:true, maintainAspectRatio:false,
    plugins:{ legend:{ position:'top', labels:{boxWidth:12,font:{size:12,family:'Inter'}} } },
    scales:{
      x:{ grid:{display:false}, ticks:{font:{family:'Inter',size:11}} },
      y:{ grid:{color:'rgba(0,0,0,0.04)'}, beginAtZero:true, ticks:{font:{family:'Inter',size:11},stepSize:1} }
    }
  }
});

// Department Donut Chart
const deptCtx = document.getElementById('deptChart').getContext('2d');
new Chart(deptCtx, {
  type: 'doughnut',
  data: {
    labels: {!! $deptStats->pluck('name')->toJson() !!},
    datasets:[{
      data: {!! $deptStats->pluck('employees_count')->toJson() !!},
      backgroundColor: ['#6366f1','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#06b6d4'],
      borderWidth: 0, borderRadius: 4,
    }]
  },
  options:{
    responsive:true, maintainAspectRatio:false,
    cutout:'70%',
    plugins:{
      legend:{position:'bottom',labels:{boxWidth:10,font:{size:11,family:'Inter'},padding:8}},
    }
  }
});
</script>
@endpush
