<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') — {{ \App\Models\Setting::get('company_name','Diamond World HRM') }}</title>
<meta name="description" content="Diamond World Human Resource Management System">
<link rel="icon" type="image/png" href="{{ asset('image/favicon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@stack('styles')
</head>
<body>

{{-- ═══ SIDEBAR ════════════════════════════════════════════════════════════ --}}
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-text" style="display:flex;flex-direction:column;gap:1px;overflow:hidden;padding-left:4px">
      <img src="{{ asset('image/dw_logo_500px_x_300px.png') }}" alt="Diamond World" style="height:70px;object-fit:contain;object-position:left;opacity:0.95;">
    </div>
  </div>

  <nav class="sidebar-nav" id="sidebarNav">

    {{-- Dashboard --}}
    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <span class="nav-icon"><i class="bi bi-grid-fill"></i></span>
      <span class="nav-label">Dashboard</span>
    </a>

    {{-- Employees --}}
    @can('view_employees')
    <div class="nav-section-label">PEOPLE</div>
    <div x-data="{ open: {{ request()->routeIs('employees.*') ? 'true' : 'false' }} }">
      <div class="nav-item" @click="open=!open" :aria-expanded="open.toString()">
        <span class="nav-icon"><i class="bi bi-people-fill"></i></span>
        <span class="nav-label">Employees</span>
        <i class="bi bi-chevron-down chevron"></i>
      </div>
      <div class="nav-submenu" x-show="open" x-transition>
        <a href="{{ route('employees.index') }}" class="nav-item {{ request()->routeIs('employees.index') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-list-ul"></i></span>
          <span class="nav-label">All Employees</span>
        </a>
        <a href="{{ route('employees.create') }}" class="nav-item {{ request()->routeIs('employees.create') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-person-plus"></i></span>
          <span class="nav-label">Add Employee</span>
        </a>
        <a href="{{ route('employees.import.form') }}" class="nav-item {{ request()->routeIs('employees.import*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-upload"></i></span>
          <span class="nav-label">Import / Export</span>
        </a>
      </div>
    </div>
    @endcan

    {{-- Organization --}}
    @can('view_all_branches')
    <div class="nav-section-label">ORGANIZATION</div>
    <div x-data="{ open: {{ request()->routeIs('branches.*','departments.*','designations.*') ? 'true' : 'false' }} }">
      <div class="nav-item" @click="open=!open" :aria-expanded="open.toString()">
        <span class="nav-icon"><i class="bi bi-building"></i></span>
        <span class="nav-label">Structure</span>
        <i class="bi bi-chevron-down chevron"></i>
      </div>
      <div class="nav-submenu" x-show="open" x-transition>
        <a href="{{ route('branches.index') }}" class="nav-item {{ request()->routeIs('branches.*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-geo-alt"></i></span>
          <span class="nav-label">Branches</span>
        </a>
        <a href="{{ route('departments.index') }}" class="nav-item {{ request()->routeIs('departments.*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-diagram-3"></i></span>
          <span class="nav-label">Departments</span>
        </a>
        <a href="{{ route('designations.index') }}" class="nav-item {{ request()->routeIs('designations.*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-tag"></i></span>
          <span class="nav-label">Designations</span>
        </a>
      </div>
    </div>
    @endcan

    {{-- Time & Shift --}}
    @canany(['manage_shifts', 'manage_holidays'])
    <div class="nav-section-label">TIME & SHIFT</div>
    @can('manage_shifts')
    <a href="{{ route('shifts.index') }}" class="nav-item {{ request()->routeIs('shifts.*') ? 'active' : '' }}">
      <span class="nav-icon"><i class="bi bi-clock"></i></span>
      <span class="nav-label">Shift Management</span>
    </a>
    @endcan
    @can('manage_holidays')
    <a href="{{ route('holidays.index') }}" class="nav-item {{ request()->routeIs('holidays.*') ? 'active' : '' }}">
      <span class="nav-icon"><i class="bi bi-calendar-x"></i></span>
      <span class="nav-label">Holidays</span>
    </a>
    @endcan
    @endcanany

    {{-- Attendance --}}
    <div x-data="{ open: {{ request()->routeIs('attendance.*') ? 'true' : 'false' }} }">
      <div class="nav-item" @click="open=!open" :aria-expanded="open.toString()">
        <span class="nav-icon"><i class="bi bi-fingerprint"></i></span>
        <span class="nav-label">Attendance</span>
        @php $extraPending = \App\Models\ExtraPresentRequest::where('status','pending')->count(); @endphp
        @if($extraPending > 0)
          <span class="nav-badge">{{ $extraPending }}</span>
        @else
          <i class="bi bi-chevron-down chevron"></i>
        @endif
      </div>
      <div class="nav-submenu" x-show="open" x-transition>
        <a href="{{ route('attendance.my') }}" class="nav-item {{ request()->routeIs('attendance.my') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-person-badge"></i></span>
          <span class="nav-label">My Attendance</span>
        </a>
        @can('manage_attendance')
        <a href="{{ route('attendance.index') }}" class="nav-item {{ request()->routeIs('attendance.index') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-calendar-day"></i></span>
          <span class="nav-label">Daily View</span>
        </a>
        <a href="{{ route('attendance.report') }}" class="nav-item {{ request()->routeIs('attendance.report') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-bar-chart-line"></i></span>
          <span class="nav-label">Reports</span>
        </a>
        <a href="{{ route('attendance.manual') }}" class="nav-item {{ request()->routeIs('attendance.manual') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-pencil-square"></i></span>
          <span class="nav-label">Manual Entry</span>
        </a>
        @endcan
        <a href="{{ route('attendance.extra') }}" class="nav-item {{ request()->routeIs('attendance.extra') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-star-fill"></i></span>
          <span class="nav-label">Extra Present</span>
          @if($extraPending > 0)<span class="nav-badge">{{ $extraPending }}</span>@endif
        </a>
      </div>
    </div>

    {{-- Leave --}}
    <div class="nav-section-label">LEAVE</div>
    <div x-data="{ open: {{ request()->routeIs('leaves.*') ? 'true' : 'false' }} }">
      <div class="nav-item" @click="open=!open" :aria-expanded="open.toString()">
        <span class="nav-icon"><i class="bi bi-calendar-check"></i></span>
        <span class="nav-label">Leave Management</span>
        @php $pendingLeaves = \App\Models\LeaveApplication::where('status','pending')->count(); @endphp
        @if($pendingLeaves > 0)
          <span class="nav-badge">{{ $pendingLeaves }}</span>
        @else
          <i class="bi bi-chevron-down chevron"></i>
        @endif
      </div>
      <div class="nav-submenu" x-show="open" x-transition>
        <a href="{{ route('leaves.index') }}" class="nav-item {{ request()->routeIs('leaves.index') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-list-check"></i></span>
          <span class="nav-label">All Applications</span>
        </a>
        <a href="{{ route('leaves.apply') }}" class="nav-item {{ request()->routeIs('leaves.apply') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-plus-circle"></i></span>
          <span class="nav-label">Apply Leave</span>
        </a>
        <a href="{{ route('leaves.balance') }}" class="nav-item {{ request()->routeIs('leaves.balance') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-bar-chart"></i></span>
          <span class="nav-label">Leave Balance</span>
        </a>
      </div>
    </div>

    {{-- Notices --}}
    <div class="nav-section-label">COMMUNICATION</div>
    <a href="{{ route('notices.index') }}" class="nav-item {{ request()->routeIs('notices.*') ? 'active' : '' }}">
      <span class="nav-icon"><i class="bi bi-megaphone"></i></span>
      <span class="nav-label">Notice Board</span>
    </a>

    {{-- Biometric --}}
    @can('manage_biometric')
    <div class="nav-section-label">BIOMETRIC</div>
    <div x-data="{ open: {{ request()->routeIs('biometric.*') ? 'true' : 'false' }} }">
      <div class="nav-item" @click="open=!open" :aria-expanded="open.toString()">
        <span class="nav-icon"><i class="bi bi-shield-lock"></i></span>
        <span class="nav-label">Biometric</span>
        <i class="bi bi-chevron-down chevron"></i>
      </div>
      <div class="nav-submenu" x-show="open" x-transition>
        <a href="{{ route('biometric.devices') }}" class="nav-item {{ request()->routeIs('biometric.devices') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-hdd-network"></i></span>
          <span class="nav-label">Devices</span>
        </a>
        <a href="{{ route('biometric.logs') }}" class="nav-item {{ request()->routeIs('biometric.logs') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-journal-text"></i></span>
          <span class="nav-label">Attendance Logs</span>
        </a>
        <a href="{{ route('biometric.mapping') }}" class="nav-item {{ request()->routeIs('biometric.mapping') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-link-45deg"></i></span>
          <span class="nav-label">Employee Mapping</span>
        </a>
      </div>
    </div>
    @endcan

    {{-- Payroll --}}
    <div class="nav-section-label">PAYROLL</div>
    <div x-data="{ open: {{ request()->routeIs('payroll.*') ? 'true' : 'false' }} }">
      <div class="nav-item" @click="open=!open" :aria-expanded="open.toString()">
        <span class="nav-icon"><i class="bi bi-cash-stack"></i></span>
        <span class="nav-label">Payroll</span>
        <i class="bi bi-chevron-down chevron"></i>
      </div>
      <div class="nav-submenu" x-show="open" x-transition>
        @can('manage_advance_salary')
        <a href="{{ route('payroll.report') }}" class="nav-item {{ request()->routeIs('payroll.report') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-file-earmark-spreadsheet"></i></span>
          <span class="nav-label">Salary Report</span>
        </a>
        @endcan
        <a href="{{ route('payroll.advance-salary.index') }}" class="nav-item {{ request()->routeIs('payroll.advance-salary.*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-wallet2"></i></span>
          <span class="nav-label">Advance Salary</span>
        </a>
      </div>
    </div>

    {{-- Inventory --}}
    @hasrole('super-admin|hr-admin|hr|branch-manager')
    <div class="nav-section-label">ASSETS</div>
    <div x-data="{ open: {{ request()->routeIs('inventory.*') ? 'true' : 'false' }} }">
      <div class="nav-item" @click="open=!open" :aria-expanded="open.toString()">
        <span class="nav-icon"><i class="bi bi-box-seam"></i></span>
        <span class="nav-label">Inventory</span>
        <i class="bi bi-chevron-down chevron"></i>
      </div>
      <div class="nav-submenu" x-show="open" x-transition>
        <a href="{{ route('inventory.products.index') }}" class="nav-item {{ request()->routeIs('inventory.products.*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-boxes"></i></span>
          <span class="nav-label">Products</span>
        </a>
        <a href="{{ route('inventory.requisitions.index') }}" class="nav-item {{ request()->routeIs('inventory.requisitions.*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-clipboard-check"></i></span>
          <span class="nav-label">Requisitions</span>
        </a>
        <a href="{{ route('inventory.purchases.index') }}" class="nav-item {{ request()->routeIs('inventory.purchases.*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-cart-plus"></i></span>
          <span class="nav-label">Purchases</span>
        </a>
        <div style="margin:8px 0; border-top:1px solid rgba(255,255,255,0.1)"></div>
        <a href="{{ route('inventory.suppliers.index') }}" class="nav-item {{ request()->routeIs('inventory.suppliers.*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-truck"></i></span>
          <span class="nav-label">Suppliers</span>
        </a>
        <a href="{{ route('inventory.categories.index') }}" class="nav-item {{ request()->routeIs('inventory.categories.*','inventory.units.*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-gear"></i></span>
          <span class="nav-label">Config (Cat/Unit)</span>
        </a>
      </div>
    </div>
    @endhasrole

    {{-- Settings --}}
    <div class="nav-section-label">SYSTEM</div>
    @can('manage_settings')
    <div x-data="{ open: {{ request()->routeIs('settings.*') ? 'true' : 'false' }} }">
      <div class="nav-item" @click="open=!open" :aria-expanded="open.toString()">
        <span class="nav-icon"><i class="bi bi-gear-fill"></i></span>
        <span class="nav-label">Settings</span>
        <i class="bi bi-chevron-down chevron"></i>
      </div>
      <div class="nav-submenu" x-show="open" x-transition>
        <a href="{{ route('settings.general') }}" class="nav-item {{ request()->routeIs('settings.general') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-sliders"></i></span>
          <span class="nav-label">General</span>
        </a>
        <a href="{{ route('settings.leave') }}" class="nav-item {{ request()->routeIs('settings.leave') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-calendar2-check"></i></span>
          <span class="nav-label">Leave Types</span>
        </a>
        <a href="{{ route('roles.index') }}" class="nav-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi bi-shield-lock"></i></span>
          <span class="nav-label">Role Management</span>
        </a>
      </div>
    </div>
    @endcan
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user dropdown">
      <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="avatar avatar-sm">
      <div class="sidebar-user-info">
        <strong>{{ Str::limit(Auth::user()->name, 18) }}</strong>
        <span>{{ Auth::user()->getRoleNames()->first() ?? 'User' }}</span>
      </div>
      <div class="dropdown-menu" id="userDropSidebar">
        <a class="dropdown-item" href="{{ route('settings.general') }}"><i class="bi bi-person"></i> Profile</a>
        <div class="dropdown-divider"></div>
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
          @csrf
          <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Logout</button>
        </form>
      </div>
    </div>
  </div>
</aside>

{{-- ═══ TOPBAR ═════════════════════════════════════════════════════════════ --}}
<header class="topbar" id="topbar">
  <button class="topbar-toggle" id="sidebarToggle" title="Toggle sidebar">
    <i class="bi bi-list"></i>
  </button>

  <div class="topbar-breadcrumb">
    <a href="{{ route('dashboard') }}"><i class="bi bi-house"></i></a>
    @hasSection('breadcrumb')
      <span class="sep">/</span>
      @yield('breadcrumb')
    @endif
  </div>

  <div class="topbar-actions">
    {{-- Branch indicator --}}
    @if(Auth::user()->branch)
      <span class="badge badge-primary" style="padding:6px 12px;">
        <i class="bi bi-geo-alt"></i> {{ Auth::user()->branch->name }}
      </span>
    @endif

    {{-- Notifications --}}
    <div class="topbar-btn dropdown" id="notifBtn" title="Notifications">
      <i class="bi bi-bell"></i>
      @php $notifCount = \App\Models\LeaveApplication::where('status','pending')->count() + \App\Models\ExtraPresentRequest::where('status','pending')->count(); @endphp
      @if($notifCount > 0)<span class="badge">{{ $notifCount }}</span>@endif
      <div class="dropdown-menu" style="min-width:280px;" id="notifMenu">
        <div style="padding:12px 16px;font-weight:700;font-size:14px;border-bottom:1px solid var(--clr-border)">Notifications</div>
        @php $pendingLeavesN = \App\Models\LeaveApplication::where('status','pending')->count();
              $pendingExtraN  = \App\Models\ExtraPresentRequest::where('status','pending')->count(); @endphp
        @if($pendingLeavesN)
          <a class="dropdown-item" href="{{ route('leaves.index','status=pending') }}">
            <i class="bi bi-calendar-check text-warning"></i> {{ $pendingLeavesN }} leave application(s) pending
          </a>
        @endif
        @if($pendingExtraN)
          <a class="dropdown-item" href="{{ route('attendance.extra') }}">
            <i class="bi bi-star-fill text-success"></i> {{ $pendingExtraN }} extra present pending
          </a>
        @endif
        @if(!$pendingLeavesN && !$pendingExtraN)
          <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:13px;">No new notifications</div>
        @endif
      </div>
    </div>

    {{-- User Menu --}}
    <div class="dropdown">
      <img src="{{ Auth::user()->avatar_url }}" alt="me" class="topbar-avatar" id="userMenuBtn">
      <div class="dropdown-menu" id="userMenu">
        <div style="padding:12px 16px;border-bottom:1px solid var(--clr-border)">
          <div style="font-weight:700;font-size:13px;">{{ Auth::user()->name }}</div>
          <div style="font-size:11px;color:var(--text-muted)">{{ Auth::user()->email }}</div>
        </div>
        <a class="dropdown-item" href="{{ route('settings.general') }}"><i class="bi bi-person-circle"></i> My Profile</a>
        <div class="dropdown-divider"></div>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="dropdown-item" style="color:var(--danger)"><i class="bi bi-box-arrow-right"></i> Logout</button>
        </form>
      </div>
    </div>
  </div>
</header>

{{-- ═══ MAIN CONTENT ═══════════════════════════════════════════════════════ --}}
<main class="main-content" id="mainContent">

  {{-- Flash Messages --}}
  @if(session('success'))
    <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
  @endif
  @if(session('warning'))
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('warning') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger"><i class="bi bi-x-circle-fill"></i> {{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <i class="bi bi-x-circle-fill"></i>
      <div><strong>Please fix the following:</strong><ul style="margin:4px 0 0 16px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    </div>
  @endif

  @yield('content')
</main>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  // ── Sidebar Toggle ──────────────────────────────────────────────────────
  const sidebar    = document.getElementById('sidebar');
  const topbar     = document.getElementById('topbar');
  const mainCon    = document.getElementById('mainContent');
  const toggleBtn  = document.getElementById('sidebarToggle');

  let collapsed = localStorage.getItem('sidebar_collapsed') === 'true';
  applyState();

  toggleBtn?.addEventListener('click', () => {
    collapsed = !collapsed;
    localStorage.setItem('sidebar_collapsed', collapsed);
    applyState();
  });

  function applyState() {
    if (collapsed) {
      sidebar.classList.add('collapsed');
      topbar.classList.add('shifted');
      mainCon.classList.add('shifted');
    } else {
      sidebar.classList.remove('collapsed');
      topbar.classList.remove('shifted');
      mainCon.classList.remove('shifted');
    }
  }

  // ── Dropdowns ───────────────────────────────────────────────────────────
  document.querySelectorAll('.dropdown').forEach(dd => {
    const trigger = dd.querySelector('[id$="Btn"], .topbar-avatar, .topbar-btn, .sidebar-user');
    const menu    = dd.querySelector('.dropdown-menu');
    if (!trigger || !menu) return;
    trigger.addEventListener('click', e => { e.stopPropagation(); menu.classList.toggle('open'); });
  });
  document.addEventListener('click', () => document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('open')));

  // ── Auto-dismiss alerts after 5s ──────────────────────────────────────
  setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
      a.style.transition = 'opacity 0.4s ease';
      a.style.opacity = '0';
      setTimeout(() => a.remove(), 400);
    });
  }, 5000);
</script>
@stack('scripts')
</body>
</html>
