@extends('layouts.app')
@section('title', 'Advanced Salary Details')
@section('breadcrumb')
    <span class="text-muted">Payroll</span>
    <span class="mx-2">/</span>
    <a href="{{ route('payroll.advance-salary.index') }}" class="text-muted">Advanced Salary</a>
    <span class="mx-2">/</span>
    <span class="current">Details</span>
@endsection

@section('content')
<div class="page-header flex justify-between align-center">
    <div>
        <h1 class="page-title">Request Details</h1>
        <p class="page-subtitle">Submitted on {{ $advanceSalary->created_at->format('M d, Y h:i A') }}</p>
    </div>
    <a href="{{ route('payroll.advance-salary.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="grid" style="grid-template-columns: 1fr 2fr; gap: 2rem;">
    <!-- LEFT: Summary Details -->
    <div class="glass-card h-100">
        <div class="glass-card-header">
            <h3 class="glass-card-title">Advance Info</h3>
            @if($advanceSalary->status == 'pending')
                <span class="badge" style="background:rgba(234,179,8,0.1);color:#eab308;">Pending</span>
            @elseif($advanceSalary->status == 'approved')
                <span class="badge" style="background:rgba(34,197,94,0.1);color:#22c55e;">Approved</span>
            @elseif($advanceSalary->status == 'rejected')
                <span class="badge" style="background:rgba(239,68,68,0.1);color:#ef4444;">Rejected</span>
            @endif
        </div>
        <div class="glass-card-body p-0">
            <table class="table mb-0">
                <tbody>
                    <tr>
                        <th style="width: 40%">Employee:</th>
                        <td>{{ $advanceSalary->employee ? $advanceSalary->employee->full_name : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Amount:</th>
                        <td><strong style="color:var(--primary)">৳{{ number_format($advanceSalary->amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Received Date:</th>
                        <td>{{ $advanceSalary->received_date->format('d M, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Deduct Start:</th>
                        <td>{{ date('F Y', strtotime($advanceSalary->start_deduct_month)) }}</td>
                    </tr>
                    <tr>
                        <th>Total Installments:</th>
                        <td>{{ $advanceSalary->installment_count }}</td>
                    </tr>
                    <tr>
                        <th>Reason:</th>
                        <td><p class="text-muted" style="white-space: pre-wrap">{{ $advanceSalary->reason ?? 'No reason provided.' }}</p></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- RIGHT: Installments schedule -->
    <div class="glass-card h-100">
        <div class="glass-card-header">
            <h3 class="glass-card-title">Installment Schedule</h3>
        </div>
        <div class="glass-card-body p-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Deduction Month</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($advanceSalary->installments as $inst)
                        <tr>
                            <td>{{ $inst->installment_no }}</td>
                            <td>{{ date('F Y', strtotime($inst->deduct_month)) }}</td>
                            <td>৳{{ number_format($inst->amount, 2) }}</td>
                            <td>
                                @if($inst->is_deducted)
                                    <span class="badge" style="background:rgba(34,197,94,0.1);color:#22c55e;">Deducted</span>
                                @else
                                    <span class="badge" style="background:rgba(107,114,128,0.1);color:#6b7280;">Upcoming</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="p-4 border-top" style="border-top-color: rgba(255,255,255,0.05) !important">
                <div class="flex justify-between" style="font-weight: 500; font-size: 16px;">
                    <span>Total Deduction Expected:</span>
                    <span>৳{{ number_format($advanceSalary->installments->sum('amount'), 2) }}</span>
                </div>
            </div>
            
            <!-- Future Approval Action buttons for HR -->
            <!-- 
            @if(Auth::user()->hasPermissionTo('manage_employees') && $advanceSalary->status == 'pending')
                <div class="flex gap-4 p-4 mt-4" style="background: rgba(0,0,0,0.2)">
                    <button class="btn btn-success"><i class="bi bi-check-lg"></i> Approve</button>
                    <button class="btn btn-danger"><i class="bi bi-x-lg"></i> Reject</button>
                </div>
            @endif
            -->
        </div>
    </div>
</div>
@endsection
