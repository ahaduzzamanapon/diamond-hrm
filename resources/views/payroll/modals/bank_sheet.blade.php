<div style="background: white; border-radius: 8px; padding: 20px; font-family: 'Inter', sans-serif;">
    <div style="text-align: right; margin-bottom: 20px;" class="no-print">
        <!-- Buttons handled by modal header -->
    </div>

    <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px;">
        <h2 style="font-weight: bold; margin: 0; font-size: 22px; color: #000;">{{ \App\Models\Setting::get('company_name','Diamond World') }}</h2>
        <p style="margin: 5px 0 0 0; color: #000;">Bank Salary Transfer Sheet for {{ \Carbon\Carbon::parse($month)->format('M-Y') }}</p>
    </div>

    <table style="width: 100%; border-collapse:collapse; color: #000;" border="1">
        <thead style="background:#f3f4f6;">
            <tr>
                <th style="padding:8px;">SL</th>
                <th style="padding:8px;">Name</th>
                <th style="padding:8px;">Designation</th>
                <th style="padding:8px;">Bank Account</th>
                <th style="padding:8px;">Net Salary</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payrolls as $idx => $p)
            <tr>
                <td style="padding:8px; text-align:center;">{{ $idx + 1 }}</td>
                <td style="padding:8px;">{{ $p->employee->full_name }}</td>
                <td style="padding:8px;">{{ $p->employee->designation->name ?? 'N/A' }}</td>
                <td style="padding:8px;">{{ $p->employee->bank_account ?? 'N/A' }}</td>
                <td style="padding:8px; text-align:right;">{{ number_format($p->net_salary, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="padding: 20px; text-align: center;">No processed bank salaries found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
