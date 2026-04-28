<div style="background: white; border-radius: 8px; padding: 20px; font-family: 'Inter', sans-serif;">
    <div style="text-align: right; margin-bottom: 20px;" class="no-print">
        <!-- Buttons handled by modal header -->
    </div>

    <style>
        .payslip-box { border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; color: black; page-break-inside: avoid; }
        .payslip-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .payslip-table th, .payslip-table td { padding: 8px; border: 1px solid #ddd; }
        .payslip-table th { background: #f3f4f6; }
        @media print {
            .no-print { display: none !important; }
            .payslip-box { border: 1px solid #000; break-inside: avoid; }
        }
    </style>

    @forelse($payrolls as $p)
    <div class="payslip-box">
        <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px;">
            <h2 style="font-weight: bold; margin: 0; font-size: 22px;">{{ \App\Models\Setting::get('company_name','Diamond World') }}</h2>
            <p style="margin: 5px 0 0 0;">Payslip for the month of {{ \Carbon\Carbon::parse($month)->format('F, Y') }}</p>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 14px;">
            <table style="width: 48%;">
                <tr><td><strong>Employee Name:</strong></td><td>{{ $p->employee->full_name }}</td></tr>
                <tr><td><strong>Employee ID:</strong></td><td>{{ $p->employee->employee_id }}</td></tr>
                <tr><td><strong>Designation:</strong></td><td>{{ $p->employee->designation->name ?? 'N/A' }}</td></tr>
            </table>
            <table style="width: 48%; text-align: right;">
                <tr><td><strong>Status:</strong></td><td>{{ ucfirst($p->status) }}</td></tr>
                <tr><td><strong>Account Number:</strong></td><td>{{ $p->employee->bank_account ?? 'Cash/None' }}</td></tr>
                <tr><td><strong>Joining Date:</strong></td><td>{{ optional($p->employee->joining_date)->format('d M Y') }}</td></tr>
            </table>
        </div>

        <div style="display: flex; gap: 20px;">
            <!-- Earnings -->
            <div style="flex: 1;">
                <table class="payslip-table">
                    <thead>
                        <tr><th colspan="2">Earnings</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Basic Salary</td><td style="text-align: right;">{{ number_format($p->basic_salary, 2) }}</td></tr>
                        <tr><td>House Rent</td><td style="text-align: right;">{{ number_format($p->house_rent, 2) }}</td></tr>
                        <tr><td>Medical</td><td style="text-align: right;">{{ number_format($p->medical, 2) }}</td></tr>
                        <tr><td>Transport</td><td style="text-align: right;">{{ number_format($p->transport, 2) }}</td></tr>
                        <tr style="font-weight: bold; background: #fafafa;"><td>Total Earnings (A)</td><td style="text-align: right;">{{ number_format($p->gross_salary, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Deductions -->
            <div style="flex: 1;">
                <table class="payslip-table">
                    <thead>
                        <tr><th colspan="2">Deductions</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Late Deduction</td><td style="text-align: right;">{{ number_format($p->late_deduction, 2) }}</td></tr>
                        <tr><td>Absent Deduction</td><td style="text-align: right;">{{ number_format($p->absent_deduction, 2) }}</td></tr>
                        <tr><td>Advance Salary</td><td style="text-align: right;">{{ number_format($p->advance_salary_deduction, 2) }}</td></tr>
                        <tr><td>Tax Deduction</td><td style="text-align: right;">{{ number_format($p->tax_deduction, 2) }}</td></tr>
                        <tr style="font-weight: bold; background: #fafafa;"><td>Total Deductions (B)</td><td style="text-align: right;">{{ number_format($p->total_deduction, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 20px; text-align: right; font-size: 18px; border-top: 2px solid #000; padding-top: 10px;">
            <strong>Net Pay (A - B): <span style="background: #e0f2fe; padding: 5px 15px; border-radius: 4px;">৳ {{ number_format($p->net_salary, 2) }}</span></strong>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-top: 60px; font-weight: bold; font-size: 14px;">
            <div style="border-top: 1px solid #000; padding-top: 5px; width: 200px; text-align: center;">Employer Signature</div>
            <div style="border-top: 1px solid #000; padding-top: 5px; width: 200px; text-align: center;">Employee Signature</div>
        </div>
    </div>
    @empty
    <div style="text-align: center; padding: 50px; color: #666;">
        <h4>No payroll processed for this month yet.</h4>
        <p>Please process salaries first to generate payslips.</p>
    </div>
    @endforelse
</div>
