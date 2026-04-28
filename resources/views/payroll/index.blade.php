@extends('layouts.app')
@section('title','Payroll Reports')
@section('breadcrumb')<span class="sep">/</span><span class="current">Payroll Reports</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Payroll / Salary Reports</h1><p class="page-subtitle">Filter, select employees to process payroll or generate sheets</p></div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

  {{-- ── LEFT: Filters + Reports ────────────────────────────────── --}}
  <div>

    {{-- Filter Card --}}
    <div class="glass-card mb-16">
      <div class="card-header"><div class="card-title"><i class="bi bi-funnel-fill"></i> Filters & Processing</div></div>
      <div class="card-body">
        
        <form action="{{ route('payroll.report') }}" method="GET" id="filterForm">
            <div class="grid g-3 gap-12">
              <div class="form-group">
                <label class="form-label">Month <span class="req">*</span></label>
                <!-- Changing this to NOT submit automatically, as JS will grab it -->
                <input type="month" id="filterMonth" name="month" class="form-control" value="{{ $month }}">
              </div>
              <div class="form-group">
                <label class="form-label">Branch</label>
                <select id="filterBranch" class="form-control" onchange="filterEmps()">
                  <option value="">All Branches</option>
                  @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Employee Status</label>
                <!-- Fully JS driven filter -->
                <select id="filterStatus" name="status" class="form-control" onchange="filterEmps()">
                  <option value="active" {{ $status=='active' ? 'selected' : '' }}>Active</option>
                  <option value="inactive" {{ $status=='inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
              </div>
            </div>
            <div class="grid g-3 gap-12 mt-4">
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
        </form>

        {{-- ── Process Buttons ──────────────────────────────────── --}}
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--clr-border)">
          <div style="font-size:11px;font-weight:700;color:var(--text-muted);letter-spacing:0.8px;text-transform:uppercase;margin-bottom:10px">Process Payroll</div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            
            <button class="proc-btn" id="procBtn1" onclick="submitProcess('process')">
              <i class="bi bi-cpu"></i> Process Draft
            </button>
            <button class="proc-btn proc-btn-range" id="procBtn2" onclick="submitProcess('final')">
              <i class="bi bi-check-all"></i> Finalize Salary
            </button>

          </div>
          <div style="margin-top:8px;font-size:11px;color:var(--text-muted)">
            Auto-fills: <strong>Basic & Allowances</strong> · <strong>Advance Deductions</strong> pulled directly from approved requests for this targeted month.
          </div>
          
          <div id="processResult" style="display:none;margin-top:10px;font-size:13px;font-weight:600;padding:10px 14px;border-radius:8px"></div>
        </div>
      </div>
    </div>

    {{-- Report Buttons --}}
    <div class="glass-card">
      <div class="card-header">
        <div class="card-title"><i class="bi bi-files"></i> Document Reports</div>
        <div style="font-size:12px;color:var(--text-muted)">Select reports to generate view →</div>
      </div>
      <div class="card-body">

        {{-- Monthly --}}
        <div style="margin-bottom:16px;">
          <div style="font-size:11px;font-weight:700;color:var(--text-muted);letter-spacing:0.8px;text-transform:uppercase;margin-bottom:10px">Salary Sheets</div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button class="rpt-btn" onclick="openReport('salary_sheet')"><i class="bi bi-list-columns"></i> Salary Sheet</button>
            <button class="rpt-btn rpt-primary" onclick="openReport('bank_sheet')"><i class="bi bi-bank"></i> Bank Salary Sheet</button>
            <button class="rpt-btn rpt-success" onclick="openReport('cash_sheet')"><i class="bi bi-cash-stack"></i> Cash Salary Sheet</button>
          </div>
        </div>

        {{-- Continuous Date Range --}}
        <div style="padding-top:14px;border-top:1px solid var(--clr-border)">
          <div style="font-size:11px;font-weight:700;color:var(--text-muted);letter-spacing:0.8px;text-transform:uppercase;margin-bottom:10px">Individual Documents</div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button class="rpt-btn rpt-info" onclick="openReport('payslip')"><i class="bi bi-person-lines-fill"></i> Salary Report / Payslip</button>
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
        <label class="emp-row" data-name="{{ strtolower($emp->name) }}" data-branch="{{ $emp->branch_id }}" data-dept="{{ $emp->department_id }}" data-desig="{{ $emp->designation_id }}" data-status="{{ strtolower($emp->status) }}">
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
      <div class="emp-count" id="selectedCount">None selected (will throw error if submitting process)</div>
    </div>
  </div>

</div>


{{-- ════ REPORT MODAL ═══════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="reportModal" style="z-index:500">
  <div class="modal" style="max-width:95vw;width:1200px;max-height:90vh;display:flex;flex-direction:column">
    <div class="modal-header" style="flex-shrink:0">
      <span class="modal-title" id="reportTitle"><i class="bi bi-table"></i> Report</span>
      <div class="flex gap-8" style="display:flex;gap:8px;">
        <button onclick="printReport()" class="btn btn-sm btn-secondary" style="padding: 6px 12px; background: #e2e8f0; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 5px; font-weight: 600; font-size: 13px;"><i class="bi bi-printer"></i> Print / PDF</button>
        <button class="modal-close" onclick="document.getElementById('reportModal').classList.remove('open')">&times;</button>
      </div>
    </div>
    <div style="overflow:auto;flex:1;padding:16px; background:#f1f5f9;" id="reportContent">
      <!-- Ajax load content -->
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
  display:flex; align-items:center; gap:10px;
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

.emp-count { padding:9px 14px; border-top:1px solid var(--clr-border); font-size:11.5px; color:var(--text-muted); font-weight:500; }

@keyframes spin { to { transform:rotate(360deg); } }

@media print {
  /* Hide absolutely everything in the body by default */
  body > * { display: none !important; }
  
  /* Show only the modal overlay, remove background */
  #reportModal { 
    display: block !important; 
    position: absolute !important; 
    top: 0 !important; left: 0 !important; 
    width: 100% !important; height: auto !important; 
    background: white !important; 
    z-index: 9999 !important;
  }
  
  /* Reset modal styles to let content flow naturally */
  .modal { 
    max-width: 100% !important; 
    width: 100% !important; 
    max-height: none !important; 
    box-shadow: none !important; 
    background: white !important; 
    margin: 0 !important; 
    padding: 0 !important;
    border: none !important;
  }
  
  /* Hide the modal header */
  .modal-header { display: none !important; }
  
  /* Let report content expand infinitely so pages don't clip */
  #reportContent { 
    overflow: visible !important; 
    height: auto !important; 
    padding: 0 !important; 
    background: white !important;
  }
  
  /* Set page to landscape size natively */
  @page { size: landscape; margin: 10mm; }
}
</style>
@endsection

@push('scripts')
<script>
const CSRF  = document.querySelector('meta[name="csrf-token"]').content;
let allSelected = false;

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
  const status = document.getElementById('filterStatus').value;
  
  document.querySelectorAll('.emp-row').forEach(row => {
    const nameMatch   = !q || row.dataset.name.includes(q);
    const branchMatch = !branch || row.dataset.branch == branch;
    const deptMatch   = !dept   || row.dataset.dept   == dept;
    const desigMatch  = !desig  || row.dataset.desig  == desig;
    const statusMatch = !status || row.dataset.status == status;
    
    if (nameMatch && branchMatch && deptMatch && desigMatch && statusMatch) {
        row.style.setProperty('display', 'flex', 'important');
    } else {
        row.style.setProperty('display', 'none', 'important');
    }
  });
}
// Run filter initially to hide inactive users on load if 'active' is default
filterEmps();

function updateCount() {
  const c = document.querySelectorAll('.emp-check:checked').length;
  document.getElementById('selectedCount').textContent = c > 0 ? `${c} employee(s) selected` : 'None selected (will throw error if submitting process)';
}

document.querySelectorAll('.emp-check').forEach(c => c.addEventListener('change', updateCount));

function submitProcess(actionType) {
    let checked = document.querySelectorAll('.emp-check:checked');
    if (checked.length === 0) {
        alert('Please select at least one employee.');
        return;
    }
    
    let month = document.getElementById('filterMonth').value;
    if(!confirm(`Are you sure you want to run the ${actionType.toUpperCase()} action on the selected employees for the month of ${month}?`)) return;
    
    let empIds = [...checked].map(c => c.value);
    
    let resDiv = document.getElementById('processResult');
    let b1 = document.getElementById('procBtn1');
    let b2 = document.getElementById('procBtn2');
    
    b1.disabled = true;
    b2.disabled = true;
    resDiv.style.display = 'none';

    fetch('{{ route("payroll.process") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ month: month, employee_ids: empIds, action_type: actionType })
    })
    .then(r => r.json())
    .then(data => {
        b1.disabled = false;
        b2.disabled = false;
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
        b1.disabled = false;
        b2.disabled = false;
        resDiv.style.cssText = 'display:block;margin-top:10px;font-size:13px;font-weight:600;padding:10px 14px;border-radius:8px;background:#fee2e2;color:#991b1b';
        resDiv.innerHTML = '<i class="bi bi-x-circle-fill"></i> Server error. Please try again.';
    });
}

function openReport(type) {
    let month = document.getElementById('filterMonth').value;
    document.getElementById('reportModal').classList.add('open');
    document.getElementById('reportContent').innerHTML = `
    <div style="text-align:center;padding:60px;color:#94a3b8">
      <div style="font-size:40px;animation:spin 1s linear infinite;display:inline-block"><i class="bi bi-arrow-clockwise"></i></div>
      <p style="margin-top:12px;font-size:14px">Loading report...</p>
    </div>`;
    
    fetch(`{{ route('payroll.modal') }}?month=${month}&type=${type}`)
    .then(r => r.text())
    .then(html => {
        document.getElementById('reportContent').innerHTML = html;
    })
    .catch(e => {
        document.getElementById('reportContent').innerHTML = '<div class="text-center p-5 text-danger">Failed to load report. Check network tabs.</div>';
    });
}

function printReport() {
  window.print();
}
</script>
@endpush
