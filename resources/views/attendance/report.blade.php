@extends('layouts.app')
@section('title','Attendance Reports')
@section('breadcrumb')<a href="{{ route('attendance.index') }}">Attendance</a><span class="sep">/</span><span class="current">Reports</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Attendance Reports</h1><p class="page-subtitle">Filter, select employees then click any report type</p></div>
  <div class="flex gap-8">
    <a href="{{ route('attendance.manual') }}" class="btn btn-secondary"><i class="bi bi-pencil-square"></i> Manual Entry</a>
    <a href="{{ route('attendance.extra') }}" class="btn btn-secondary"><i class="bi bi-star"></i> Extra Present</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

  {{-- ── LEFT: Filters + Report Buttons ────────────────────────────────── --}}
  <div>

    {{-- Filter Card --}}
    <div class="glass-card mb-16">
      <div class="card-header"><div class="card-title"><i class="bi bi-funnel-fill"></i> Filters</div></div>
      <div class="card-body">
        <div class="grid g-3 gap-12">
          <div class="form-group">
            <label class="form-label">First Date <span class="req">*</span></label>
            <input type="date" id="date1" class="form-control" value="{{ date('Y-m-d') }}">
          </div>
          <div class="form-group">
            <label class="form-label">Second Date <small class="text-muted">(for range)</small></label>
            <input type="date" id="date2" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Month</label>
            <input type="month" id="filterMonth" class="form-control" value="{{ date('Y-m') }}">
          </div>
        </div>
        <div class="grid g-3 gap-12 mt-4">
          <div class="form-group">
            <label class="form-label">Branch</label>
            <select id="filterBranch" class="form-control" onchange="filterEmps()">
              <option value="">All Branches</option>
              @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Department</label>
            <select id="filterDept" class="form-control" onchange="filterEmps()">
              <option value="">All Departments</option>
              @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Designation</label>
            <select id="filterDesig" class="form-control" onchange="filterEmps()">
              <option value="">All Designations</option>
              @foreach($designations as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
            </select>
          </div>
        </div>

        {{-- ── Process Buttons ──────────────────────────────────── --}}
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--clr-border)">
          <div style="font-size:11px;font-weight:700;color:var(--text-muted);letter-spacing:0.8px;text-transform:uppercase;margin-bottom:10px">Process Attendance</div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">

            {{-- Single date --}}
            <button class="proc-btn" id="processBtn" onclick="processAttendance('single')">
              <i class="bi bi-cpu"></i> Process (Single Date)
            </button>

            {{-- Date range --}}
            <button class="proc-btn proc-btn-range" id="processBtnRange" onclick="processAttendance('range')">
              <i class="bi bi-calendar-range"></i> Date Between Process
            </button>

            {{-- Extra Present option --}}
            <label style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:#0a0a0a;cursor:pointer;white-space:nowrap">
              <input type="checkbox" id="flagExtraPresent" style="accent-color:#0a0a0a;width:14px;height:14px">
              Flag Extra Present
            </label>
          </div>
          <div style="margin-top:8px;font-size:11px;color:var(--text-muted)">
            Auto-fills: <strong>Absent</strong> for missing · <strong>Weekend/Holiday</strong> for off-days · 
            <strong>Extra Present</strong> flag for weekend/holiday punches (if checked)
          </div>
          <div id="processResult" style="display:none;margin-top:10px;font-size:13px;font-weight:600;padding:10px 14px;border-radius:8px"></div>
        </div>
      </div>
    </div>

    {{-- Report Buttons --}}
    <div class="glass-card">
      <div class="card-header">
        <div class="card-title"><i class="bi bi-files"></i> Employee Reports</div>
        <div style="font-size:12px;color:var(--text-muted)">Select employees from the list →</div>
      </div>
      <div class="card-body">

        {{-- Daily & Date Range --}}
        <div style="margin-bottom:16px">
          <div style="font-size:11px;font-weight:700;color:var(--text-muted);letter-spacing:0.8px;text-transform:uppercase;margin-bottom:10px">Daily / Date Range</div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button class="rpt-btn" onclick="loadReport('all')" data-mode="date"><i class="bi bi-list-ul"></i> All</button>
            <button class="rpt-btn rpt-success" onclick="loadReport('present')" data-mode="date"><i class="bi bi-check-circle"></i> Present</button>
            <button class="rpt-btn rpt-danger" onclick="loadReport('absent')" data-mode="date"><i class="bi bi-x-circle"></i> Absent</button>
            <button class="rpt-btn rpt-warning" onclick="loadReport('late')" data-mode="date"><i class="bi bi-clock"></i> Late</button>
            <button class="rpt-btn rpt-info" onclick="loadReport('early_out')" data-mode="date"><i class="bi bi-box-arrow-left"></i> Early Out</button>
            <button class="rpt-btn" onclick="loadReport('leave')" data-mode="date"><i class="bi bi-calendar-x"></i> On Leave</button>
            <button class="rpt-btn rpt-warning" onclick="loadReport('late_comment')" data-mode="date"><i class="bi bi-chat-left-text"></i> Late Comment</button>
          </div>
        </div>

        {{-- Monthly --}}
        <div style="margin-bottom:16px;padding-top:14px;border-top:1px solid var(--clr-border)">
          <div style="font-size:11px;font-weight:700;color:var(--text-muted);letter-spacing:0.8px;text-transform:uppercase;margin-bottom:10px">Monthly</div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button class="rpt-btn rpt-primary" onclick="loadReport('monthly_summary')" data-mode="month"><i class="bi bi-table"></i> Attn Summary</button>
            <button class="rpt-btn rpt-primary" onclick="loadReport('monthly_register')" data-mode="month"><i class="bi bi-journal-text"></i> Register Sheet</button>
            <button class="rpt-btn" onclick="loadReport('monthly_present')" data-mode="month"><i class="bi bi-check2-all"></i> Monthly Present</button>
            <button class="rpt-btn rpt-danger" onclick="loadReport('monthly_absent')" data-mode="month"><i class="bi bi-x-lg"></i> Monthly Absent</button>
          </div>
        </div>

        {{-- Continuous Date Range --}}
        <div style="padding-top:14px;border-top:1px solid var(--clr-border)">
          <div style="font-size:11px;font-weight:700;color:var(--text-muted);letter-spacing:0.8px;text-transform:uppercase;margin-bottom:10px">Continuous (Date Range)</div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button class="rpt-btn rpt-success" onclick="loadReport('continuous')" data-mode="range"><i class="bi bi-calendar-range"></i> Full Register</button>
            <button class="rpt-btn" onclick="loadReport('performance')" data-mode="range"><i class="bi bi-graph-up"></i> Performance</button>
            <button class="rpt-btn rpt-warning" onclick="loadReport('late_analysis')" data-mode="range"><i class="bi bi-clock-history"></i> Late Analysis</button>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- ── RIGHT: Employee Selection ───────────────────────────────────────── --}}
  <div class="glass-card" style="position:sticky;top:80px">
    <div class="card-header">
      <div class="card-title"><i class="bi bi-people"></i> Employees</div>
      <button class="btn btn-sm btn-secondary" onclick="toggleAll()">Select All</button>
    </div>
    <div class="card-body" style="padding:0">
      <div style="padding:10px 14px;border-bottom:1px solid var(--clr-border)">
        <input type="text" id="empSearch" class="form-control" placeholder="Search employee..." oninput="filterEmps()" style="font-size:13px">
      </div>
      <div style="max-height:500px;overflow-y:auto" id="empList">
        @foreach($employees as $emp)
        @php $initials = collect(explode(' ',$emp->name))->map(fn($w)=>strtoupper($w[0]))->take(2)->implode(''); @endphp
        <label class="emp-row" data-name="{{ strtolower($emp->name) }}" data-branch="{{ $emp->branch_id }}" data-dept="{{ $emp->department_id }}" data-desig="{{ $emp->designation_id }}">
          <input type="checkbox" class="emp-check" value="{{ $emp->id }}">
          <div class="emp-avatar">
            <img src="{{ $emp->photo_url }}" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <span>{{ $initials }}</span>
          </div>
          <div class="emp-info">
            <div class="emp-name">{{ $emp->name }}</div>
            <div class="emp-sub">{{ $emp->employee_id }} · {{ $emp->department?->name }}</div>
          </div>
        </label>
        @endforeach
      </div>
      <div class="emp-count" id="selectedCount">None selected (all employees will be used)</div>
    </div>
  </div>

</div>

{{-- ════ REPORT MODAL ═══════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="reportModal" style="z-index:500">
  <div class="modal" style="max-width:95vw;width:1100px;max-height:90vh;display:flex;flex-direction:column">
    <div class="modal-header" style="flex-shrink:0">
      <span class="modal-title" id="reportTitle"><i class="bi bi-table"></i> Report</span>
      <div class="flex gap-8">
        <button onclick="printReport()" class="btn btn-sm btn-secondary"><i class="bi bi-printer"></i> Print / PDF</button>
        <button class="modal-close" onclick="document.getElementById('reportModal').classList.remove('open')">&times;</button>
      </div>
    </div>
    <div style="overflow:auto;flex:1;padding:16px" id="reportContent">
      <div style="text-align:center;padding:40px;color:var(--text-muted)">
        <i class="bi bi-arrow-clockwise" style="font-size:32px;animation:spin 1s linear infinite"></i>
        <p style="margin-top:10px">Loading report...</p>
      </div>
    </div>
  </div>
</div>

<style>
/* Report buttons */
.rpt-btn {
  display:inline-flex; align-items:center; gap:6px;
  padding:7px 14px; border-radius:8px; font-size:12.5px; font-weight:600;
  cursor:pointer; border:1.5px solid #e2e8f0; background:#f8fafc;
  color:#0f172a; transition:all 0.15s; font-family:inherit;
}
.rpt-btn:hover { background:#0a0a0a; color:#fff; border-color:#0a0a0a; transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,0.12); }
.rpt-btn.rpt-primary  { border-color:#6366f1; color:#6366f1; }
.rpt-btn.rpt-success  { border-color:#10b981; color:#10b981; }
.rpt-btn.rpt-danger   { border-color:#ef4444; color:#ef4444; }
.rpt-btn.rpt-warning  { border-color:#f59e0b; color:#b45309; }
.rpt-btn.rpt-info     { border-color:#06b6d4; color:#0891b2; }
.rpt-btn:hover.rpt-primary  { background:#6366f1; border-color:#6366f1; color:#fff; }
.rpt-btn:hover.rpt-success  { background:#10b981; border-color:#10b981; color:#fff; }
.rpt-btn:hover.rpt-danger   { background:#ef4444; border-color:#ef4444; color:#fff; }
.rpt-btn:hover.rpt-warning  { background:#f59e0b; border-color:#f59e0b; color:#fff; }
.rpt-btn:hover.rpt-info     { background:#06b6d4; border-color:#06b6d4; color:#fff; }

/* Process buttons */
.proc-btn {
  display:inline-flex; align-items:center; gap:7px;
  padding:10px 18px; border-radius:10px; font-size:13px; font-weight:700;
  cursor:pointer; border:2px solid #0a0a0a; background:#0a0a0a;
  color:#fff; font-family:inherit; transition:all 0.2s;
}
.proc-btn:hover { background:#fff; color:#0a0a0a; }
.proc-btn:disabled { opacity:0.6; cursor:not-allowed; }
.proc-btn-range { background:#fff; color:#0a0a0a; border-color:#0a0a0a; }
.proc-btn-range:hover { background:#0a0a0a; color:#fff; }

/* Employee list */
.emp-row {
  display:flex !important; align-items:center; gap:10px;
  padding:9px 14px; cursor:pointer;
  border-bottom:1px solid var(--clr-border);
  transition:background 0.15s;
}
.emp-row:hover { background:#f8fafc; }
.emp-check { accent-color:#0a0a0a; width:15px; height:15px; flex-shrink:0; cursor:pointer; }
.emp-avatar {
  width:30px; height:30px; border-radius:50%; flex-shrink:0;
  background:#e2e8f0; overflow:hidden; position:relative;
  display:flex; align-items:center; justify-content:center;
  font-size:11px; font-weight:700; color:#475569;
}
.emp-avatar img { width:100%; height:100%; object-fit:cover; }
.emp-avatar span { display:none; }
.emp-info { flex:1; min-width:0; }
.emp-name { font-weight:600; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#0a0a0a; }
.emp-sub  { font-size:10px; color:var(--text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.emp-count { padding:9px 14px; border-top:1px solid var(--clr-border); font-size:11.5px; color:var(--text-muted); font-weight:500; }

@keyframes spin { to { transform:rotate(360deg); } }
@media print {
  .topbar, .sidebar, .page-header, aside, header, .sidebar-overlay,
  .glass-card:not(#reportPrint), .modal-header, .filter-bar { display:none !important; }
  #reportContent { padding:0 !important; }
  body { background:#fff; }
}
</style>
@endsection

@push('scripts')
<script>
const CSRF  = document.querySelector('meta[name="csrf-token"]').content;
let allSelected = false;

function getFilters(mode) {
  const empIds = [...document.querySelectorAll('.emp-check:checked')].map(c=>c.value);
  return {
    type:         '',
    mode:         mode,
    date1:        document.getElementById('date1').value,
    date2:        document.getElementById('date2').value || document.getElementById('date1').value,
    month:        document.getElementById('filterMonth').value,
    branch_id:    document.getElementById('filterBranch').value,
    dept_id:      document.getElementById('filterDept').value,
    desig_id:     document.getElementById('filterDesig').value,
    employee_ids: empIds,
  };
}

function loadReport(type) {
  const mode = document.querySelector(`[onclick="loadReport('${type}')"]`)?.dataset.mode || 'date';
  const filters = { ...getFilters(mode), type };

  // Open modal + show loader
  document.getElementById('reportModal').classList.add('open');
  const titles = {
    'all': 'All Attendance', 'present': 'Present Report', 'absent': 'Absent Report',
    'late': 'Late Report', 'early_out': 'Early Out Report', 'leave': 'Leave Report',
    'late_comment': 'Late Comment Report', 'monthly_summary': 'Attendance Summary Statement',
    'monthly_register': 'Attendance Register Statement', 'monthly_present': 'Monthly Present',
    'monthly_absent': 'Monthly Absent', 'continuous': 'Continuous Register',
    'performance': 'Attendance Performance', 'late_analysis': 'Late Analysis'
  };
  document.getElementById('reportTitle').innerHTML = `<i class="bi bi-table"></i> ${titles[type]||type}`;
  document.getElementById('reportContent').innerHTML = `
    <div style="text-align:center;padding:60px;color:#94a3b8">
      <div style="font-size:40px;animation:spin 1s linear infinite;display:inline-block"><i class="bi bi-arrow-clockwise"></i></div>
      <p style="margin-top:12px;font-size:14px">Loading report...</p>
    </div>`;

  fetch('{{ route("attendance.report.data") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    body: JSON.stringify(filters)
  })
  .then(r => r.json())
  .then(data => {
    if (data.html) {
      document.getElementById('reportContent').innerHTML = data.html;
    } else {
      document.getElementById('reportContent').innerHTML = `<div class="empty-state"><div class="empty-icon">📋</div><h3>${data.message||'No data found'}</h3></div>`;
    }
  })
  .catch(e => {
    document.getElementById('reportContent').innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> Error loading report. Please try again.</div>`;
  });
}

function printReport() {
  const content = document.getElementById('reportContent').innerHTML;
  const win = window.open('', '_blank');
  win.document.write(`<!DOCTYPE html><html><head>
    <title>Attendance Report</title>
    <style>
      body { font-family: 'Inter', Arial, sans-serif; margin: 20px; color: #000; }
      table { width: 100%; border-collapse: collapse; font-size: 11px; }
      th, td { border: 1px solid #999; padding: 5px 7px; }
      thead th { background: #1a1a1a; color: #fff; text-align: center; }
      .badge { padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; }
      .text-success { color: #10b981; } .text-danger { color: #ef4444; } .text-warning { color: #f59e0b; }
      .att-present { color: #10b981; } .att-absent { color: #ef4444; } .att-late { color: #f59e0b; }
      @page { margin: 15mm; }
    </style>
  </head><body>${content}</body></html>`);
  win.document.close();
  setTimeout(() => { win.print(); }, 500);
}

function toggleAll() {
  allSelected = !allSelected;
  document.querySelectorAll('.emp-check').forEach(c => c.checked = allSelected);
  updateCount();
}

function filterEmps() {
  const q = document.getElementById('empSearch').value.toLowerCase();
  const branch = document.getElementById('filterBranch').value;
  const dept   = document.getElementById('filterDept').value;
  const desig  = document.getElementById('filterDesig').value;
  
  document.querySelectorAll('.emp-row').forEach(row => {
    const nameMatch   = !q || row.dataset.name.includes(q);
    const branchMatch = !branch || row.dataset.branch == branch;
    const deptMatch   = !dept   || row.dataset.dept   == dept;
    const desigMatch  = !desig  || row.dataset.desig  == desig;
    
    // Use property overrides in case !important is used internally or externally
    if (nameMatch && branchMatch && deptMatch && desigMatch) {
        row.style.setProperty('display', 'flex', 'important');
    } else {
        row.style.setProperty('display', 'none', 'important');
    }
  });
}

function updateCount() {
  const c = document.querySelectorAll('.emp-check:checked').length;
  document.getElementById('selectedCount').textContent = c > 0 ? `${c} employee(s) selected` : 'None selected (all will be used)';
}

document.querySelectorAll('.emp-check').forEach(c => c.addEventListener('change', updateCount));

// ── Process Attendance ────────────────────────────────────────────────────
function processAttendance(mode) {
  const date1 = document.getElementById('date1').value;
  const date2 = document.getElementById('date2').value;
  const flagExtra = document.getElementById('flagExtraPresent').checked;

  if (!date1) { alert('Please set First Date.'); return; }
  if (mode === 'range' && !date2) { alert('Please set Second Date for date range processing.'); return; }

  const d2 = mode === 'range' ? date2 : date1;
  const label = mode === 'range' ? `${date1} → ${d2}` : date1;
  const empIds = [...document.querySelectorAll('.emp-check:checked')].map(c=>c.value);
  const branch = document.getElementById('filterBranch').value;
  const dept   = document.getElementById('filterDept').value;
  const resDiv = document.getElementById('processResult');
  const btns   = document.querySelectorAll('.proc-btn');

  if (!confirm(`Process attendance for ${label}?\n\n• Missing records → ABSENT\n• Weekends → WEEKEND\n• Holidays → HOLIDAY${flagExtra ? '\n• Existing weekend/holiday punches → EXTRA PRESENT flag' : ''}\n\nExisting records will NOT be overwritten.`)) return;

  btns.forEach(b => { b.disabled = true; });
  resDiv.style.display = 'none';

  fetch('{{ route("attendance.process") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    body: JSON.stringify({ date1, date2: d2, employee_ids: empIds, branch_id: branch, dept_id: dept, flag_extra: flagExtra })
  })
  .then(r => r.json())
  .then(data => {
    btns.forEach(b => { b.disabled = false; });
    resDiv.style.display = 'block';
    if (data.success) {
      resDiv.style.cssText = 'display:block;margin-top:10px;font-size:13px;font-weight:600;padding:10px 14px;border-radius:8px;background:#d1fae5;color:#065f46';
      resDiv.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${data.message}`;
    } else {
      resDiv.style.cssText = 'display:block;margin-top:10px;font-size:13px;font-weight:600;padding:10px 14px;border-radius:8px;background:#fee2e2;color:#991b1b';
      resDiv.innerHTML = `<i class="bi bi-x-circle-fill"></i> ${data.message || 'Error occurred'}`;
    }
  })
  .catch(() => {
    btns.forEach(b => { b.disabled = false; });
    resDiv.style.cssText = 'display:block;margin-top:10px;font-size:13px;font-weight:600;padding:10px 14px;border-radius:8px;background:#fee2e2;color:#991b1b';
    resDiv.innerHTML = '<i class="bi bi-x-circle-fill"></i> Server error. Please try again.';
  });
}
</script>
@endpush
