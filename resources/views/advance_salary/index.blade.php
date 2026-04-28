@extends('layouts.app')
@section('title', 'Advanced Salary')
@section('breadcrumb')
    <span class="text-muted">Payroll</span>
    <span class="mx-2">/</span>
    <span class="current">Advanced Salary</span>
@endsection

@section('content')
<div style="padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px;">
    <h3 style="margin-bottom: 25px; color: #333; font-weight: 600; font-size: 20px;">Advanced Salary</h3>

    <form action="{{ route('payroll.advance-salary.store') }}" method="POST" id="advanceSalaryForm">
        @csrf
        
        <div class="row" style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 25px;">
            @if($isHr)
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500;">Employee</label>
                <select name="employee_id" id="employee_id" class="form-control" style="background:#fff; border: 1px solid #ddd; padding: 10px; border-radius: 4px;" required>
                    <option value="">-- Select Employee --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->employee_id }} - {{ $emp->first_name }} {{ $emp->last_name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            
            <div style="flex: 1; min-width: 150px;">
                <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500;">Amount</label>
                <input type="number" step="0.01" class="form-control" name="amount" id="amount" style="background:#fff; border: 1px solid #ddd; padding: 10px; border-radius: 4px;" required>
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500;">Received Date</label>
                <input type="date" class="form-control" name="received_date" id="received_date" value="{{ date('Y-m-d') }}" style="background:#fff; border: 1px solid #ddd; padding: 10px; border-radius: 4px;" required>
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500;">Start Deduct Month</label>
                <input type="month" class="form-control" name="start_deduct_month" id="start_deduct_month" style="background:#fff; border: 1px solid #ddd; padding: 10px; border-radius: 4px;" required>
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500;">Instalment</label>
                <input type="number" class="form-control" name="installment_count" id="installment_count" min="1" style="background:#fff; border: 1px solid #ddd; padding: 10px; border-radius: 4px;" required>
            </div>
        </div>

        <!-- DYNAMIC INSTALLMENTS CONTAINER -->
        <div id="installmentsContainer"></div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500;">Reason</label>
            <textarea class="form-control" name="reason" rows="4" placeholder="Enter reason" style="background:#fff; border: 1px solid #ddd; padding: 10px; border-radius: 4px; resize: vertical;"></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 15px;">
            <button type="submit" name="action" value="submit" class="btn" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; font-weight: 500; border-radius: 4px; cursor: pointer;">Submit Request</button>
            <button type="submit" name="action" value="draft" class="btn" style="background: #f59e0b; color: white; border: none; padding: 10px 20px; font-weight: 500; border-radius: 4px; cursor: pointer;">Save as Draft</button>
        </div>
    </form>
</div>

<div style="padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
    <h3 style="margin-bottom: 20px; color: #333; font-weight: 600; font-size: 18px;">Requested List</h3>
    
    <div class="table-responsive">
        <table class="table" id="dataTable" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #eee; text-align: left;">
                    <th style="padding: 12px 8px; color: #666; font-weight: 600; font-size: 14px;">Sl.</th>
                    @if($isHr)<th style="padding: 12px 8px; color: #666; font-weight: 600; font-size: 14px;">Employee</th>@endif
                    <th style="padding: 12px 8px; color: #666; font-weight: 600; font-size: 14px;">Type</th>
                    <th style="padding: 12px 8px; color: #666; font-weight: 600; font-size: 14px;">Amount</th>
                    <th style="padding: 12px 8px; color: #666; font-weight: 600; font-size: 14px;">Received Date</th>
                    <th style="padding: 12px 8px; color: #666; font-weight: 600; font-size: 14px;">Deduct Month</th>
                    <th style="padding: 12px 8px; color: #666; font-weight: 600; font-size: 14px;">Instalment</th>
                    <th style="padding: 12px 8px; color: #666; font-weight: 600; font-size: 14px;">Status</th>
                    <th style="padding: 12px 8px; color: #666; font-weight: 600; font-size: 14px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($advanceSalaries as $idx => $adv)
                    <tr style="border-bottom: 1px solid #f5f5f5;">
                        <td style="padding: 12px 8px; color: #444;">{{ $idx + 1 }}</td>
                        @if($isHr)<td style="padding: 12px 8px; color: #444;">{{ $adv->employee ? $adv->employee->full_name : 'N/A' }}</td>@endif
                        <td style="padding: 12px 8px; color: #444;">Advanced</td>
                        <td style="padding: 12px 8px; color: #444;">{{ number_format($adv->amount, 0) }}</td>
                        <td style="padding: 12px 8px; color: #444;">{{ $adv->received_date->format('d-m-Y') }}</td>
                        <td style="padding: 12px 8px; color: #444;">{{ date('F Y', strtotime($adv->start_deduct_month)) }}</td>
                        <td style="padding: 12px 8px; color: #444;">{{ $adv->installment_count }}</td>
                        <td style="padding: 12px 8px;">
                            @if($adv->status == 'pending')
                                <span style="display:inline-block; border: 1px solid #eab308; color: #eab308; padding: 4px 12px; border-radius: 20px; font-size: 12px;"><i class="bi bi-pause-circle"></i> Pending</span>
                            @else
                                <span style="display:inline-block; border: 1px solid currentColor; color: #6b7280; padding: 4px 12px; border-radius: 20px; font-size: 12px;">{{ ucfirst($adv->status) }}</span>
                            @endif
                        </td>
                        <td style="padding: 12px 8px;">
                            <a href="{{ route('payroll.advance-salary.show', $adv) }}" class="btn btn-sm" style="background:#3b82f6; color:#fff; border-radius:4px; padding:4px 10px; text-decoration:none; font-size:12px;">Details</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const amountInput = document.getElementById('amount');
    const startMonthInput = document.getElementById('start_deduct_month');
    const countInput = document.getElementById('installment_count');
    const container = document.getElementById('installmentsContainer');

    function calculateInstallments() {
        const amount = parseFloat(amountInput.value);
        const startMonthRaw = startMonthInput.value; // YYYY-MM
        const count = parseInt(countInput.value);

        if (!amount || !startMonthRaw || !count || count < 1) {
            container.innerHTML = '';
            return;
        }

        const [startYear, startMonthRawStr] = startMonthRaw.split('-');
        let currentYear = parseInt(startYear);
        let currentMonth = parseInt(startMonthRawStr); // 1-12

        // Math
        let baseInstallment = Math.floor(amount / count);
        let remainder = amount - (baseInstallment * count);

        let html = '';

        for (let i = 0; i < count; i++) {
            let instAmount = baseInstallment;
            // Add remainder to the final installment
            if (i === count - 1) {
                instAmount += remainder;
            }

            // Month display logic
            let displayMonthName = new Date(currentYear, currentMonth - 1).toLocaleString('default', { month: 'long' });
            let displayString = `${displayMonthName} ${currentYear}`;
            let valueString = `${currentYear}-${currentMonth.toString().padStart(2, '0')}`; // YYYY-MM

            html += `
                <div style="margin-bottom: 15px;">
                    <div style="color: #666; font-weight: 500; font-size: 14px; margin-bottom: 8px;">Installment ${i + 1}</div>
                    <div style="display: flex; gap: 20px;">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 5px; color: #555; font-size: 12px; font-weight: 500;">Amount:</label>
                            <input type="number" step="0.01" class="form-control" name="installments[${i}][amount]" value="${instAmount.toFixed(0)}" style="background:#fff; border: 1px solid #ddd; padding: 10px; border-radius: 4px; width:100%;" readonly>
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 5px; color: #555; font-size: 12px; font-weight: 500;">Month:</label>
                            <input type="text" class="form-control" value="${displayString}" style="background:#fff; border: 1px solid #ddd; padding: 10px; border-radius: 4px; width:100%;" readonly>
                            <input type="hidden" name="installments[${i}][month]" value="${valueString}">
                        </div>
                    </div>
                </div>
            `;

            // Increment Month/Year
            currentMonth++;
            if (currentMonth > 12) {
                currentMonth = 1;
                currentYear++;
            }
        }

        container.innerHTML = html;
    }

    amountInput.addEventListener('input', calculateInstallments);
    startMonthInput.addEventListener('change', calculateInstallments);
    countInput.addEventListener('input', calculateInstallments);

    // Init datatable
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#dataTable').DataTable({
                "pageLength": 10,
                "ordering": false
            });
        }
    });
</script>
@endpush
