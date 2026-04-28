@extends('layouts.app')
@section('title','Import Employees')
@section('breadcrumb')<a href="{{ route('employees.index') }}">Employees</a> &rsaquo; <span class="current">Import</span>@endsection

@section('content')
<div class="page-header">
  <div><h1 class="page-title">Import Employees</h1><p class="page-subtitle">Bulk upload employees from Excel/CSV</p></div>
  <a href="{{ route('employees.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

@if(session('success'))
  <div class="alert alert-success mb-16"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif
@if(session('warning'))
  <div class="alert alert-warning mb-16"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('warning') }}</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
  <div class="glass-card">
    <div class="card-header"><div class="card-title"><i class="bi bi-upload"></i> Upload File</div></div>
    <form method="POST" action="{{ route('employees.import') }}" enctype="multipart/form-data">
      @csrf
      <div class="card-body">
        @if($errors->any())
          <div class="alert alert-danger mb-12"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <div class="form-group">
          <label class="form-label">Excel / CSV File <span class="req">*</span></label>
          <input type="file" name="file" class="form-control" accept=".xlsx,.csv" required>
          <div class="text-muted fs-12 mt-4">Accepted: .xlsx, .csv — Max 10MB</div>
        </div>
      </div>
      <div style="padding:14px 20px;border-top:1px solid var(--clr-border);display:flex;gap:8px;justify-content:flex-end">
        <a href="{{ route('employees.sample') }}" class="btn btn-secondary"><i class="bi bi-download"></i> Download Sample</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Import</button>
      </div>
    </form>
  </div>

  <div class="glass-card">
    <div class="card-header"><div class="card-title"><i class="bi bi-info-circle"></i> Import Instructions</div></div>
    <div class="card-body">
      <ol style="font-size:13px;color:var(--text-secondary);line-height:1.9;padding-left:18px">
        <li>Download the <a href="{{ route('employees.sample') }}" class="text-primary fw-600">Sample Excel file</a></li>
        <li>Fill in employee data — do not change column headers</li>
        <li>Required columns: <code>first_name, branch_code, department_name, designation_name, joining_date, contact_number, status</code></li>
        <li>Save as <strong>.xlsx</strong> or <strong>.csv</strong></li>
        <li>Upload the file and click <strong>Import</strong></li>
        <li>Any errors will be shown after processing</li>
      </ol>
    </div>
  </div>
</div>

{{-- Import Errors --}}
@if(session('import_errors') && count(session('import_errors')) > 0)
<div class="glass-card mt-16">
  <div class="card-header"><div class="card-title" style="color:var(--danger)"><i class="bi bi-exclamation-triangle"></i> Import Errors</div></div>
  <div class="table-wrapper">
    <table>
      <thead><tr><th>Row</th><th>Field</th><th>Error</th></tr></thead>
      <tbody>
        @foreach(session('import_errors') as $err)
        <tr>
          <td class="fs-13">{{ $err['row'] ?? '—' }}</td>
          <td class="fs-13"><code>{{ $err['field'] ?? '—' }}</code></td>
          <td class="fs-13 text-danger">{{ $err['message'] ?? $err }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
