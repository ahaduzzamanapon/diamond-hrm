<div style="background: white; border-radius: 8px; padding: 20px;">
    <!-- Top Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <div style="flex: 1;"></div>
        <div style="flex: 2; text-align: center; color: black !important;">
            <h2 style="font-weight: bold; margin-bottom: 5px; font-size: 20px;">{{ \App\Models\Setting::get('company_name','Diamond World') }}</h2>
            <p style="margin: 0; font-size: 12px; font-weight: bold;">{{ \App\Models\Setting::get('company_address','Dhaka, Bangladesh') }}</p>
            <p style="margin: 0; font-size: 12px; font-weight: bold;">Salary Month {{ \Carbon\Carbon::parse($month)->format('M-Y') }}</p>
            <p style="margin: 0; font-size: 11px;">P=Present, A=Absent, L=Late, E=Extra Pay, W=Weekend, H=Holiday, ABA=Before After Absent</p>
        </div>
        <div style="flex: 1; text-align: right;" class="no-print">
            <!-- Buttons handled by header now! -->
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive" style="overflow-x: auto;">
        <style>
            .sal-sheet { width: 100%; border-collapse: collapse; font-size: 11px; text-align: center; color: black; border: 1px solid #000; }
            .sal-sheet th, .sal-sheet td { border: 1px solid #000; padding: 4px 2px; vertical-align: middle;}
            .bg-status { background-color: #d1e7ef !important; } /* light blue */
            .bg-leave { background-color: #e5e7eb !important; } /* light grey */
            .bg-late { background-color: #e0f2fe !important; } /* very light blue */
            .bg-deduction { background-color: #fce7f3 !important; } /* pink */
            .bg-net { background-color: #dcfce7 !important; } /* light green */
            @media print {
                .no-print { display: none !important; }
            }
        </style>

        <table class="sal-sheet">
            <thead>
                <tr style="background-color: #e5e7eb;">
                    <th rowspan="2" style="width: 2%;">SL</th>
                    <th rowspan="2" style="width: 8%;">Name</th>
                    <th rowspan="2" style="width: 8%;">Degi</th>
                    <th rowspan="2" style="width: 5%;">Join.d</th>
                    <th rowspan="2" style="width: 5%;">Salary</th>
                    <th colspan="6" class="bg-status">Status</th>
                    <th colspan="2" class="bg-leave">Leave</th>
                    <th colspan="3" class="bg-late">Late</th>
                    <th colspan="4" class="bg-deduction">Deduction</th>
                    <th rowspan="2" style="width: 5%;">Net Salary</th>
                    <th rowspan="2" style="width: 3%;">Extra<br>Pay</th>
                    <th rowspan="2" style="width: 3%;">D.A<br>Day</th>
                    <th rowspan="2" style="width: 4%;">D.A Salary</th>
                    <th rowspan="2" class="bg-net" style="width: 5%;">Grand Net<br>Salary</th>
                    <th rowspan="2" style="width: 6%;">Account Number</th>
                </tr>
                <tr style="background-color: #e5e7eb;">
                    <th class="bg-status">P</th><th class="bg-status">A</th><th class="bg-status">W</th><th class="bg-status">H</th><th class="bg-status">E.P</th><th class="bg-status">ABA</th>
                    <th class="bg-leave">E</th><th class="bg-leave">S</th>
                    <th class="bg-late">Day</th><th class="bg-late">D.Day</th><th class="bg-late">Late</th>
                    <th class="bg-deduction">Abse</th><th class="bg-deduction">BA</th><th class="bg-deduction">Adv</th><th class="bg-deduction">Lunch</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $gtSalary = 0; 
                    $gtLateDed = 0;
                    $gtAbseDed = 0;
                    $gtAdvDed = 0;
                    $gtLunch = 0;
                    $gtNet = 0;
                    $gtDaSal = 0;
                    $gtGrandNet = 0;
                @endphp
                @forelse($payrolls as $idx => $p)
                    @php
                        // Calc totals inline for demo
                        $gtSalary += $p->basic_salary;
                        $gtLateDed += $p->late_deduction;
                        $gtAbseDed += $p->absent_deduction;
                        $gtAdvDed += $p->advance_salary_deduction;
                        // Assuming lunch and DA as 0
                        $gtNet += $p->net_salary;
                        $gtGrandNet += $p->net_salary;
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $p->employee->full_name }}</td>
                        <td>{{ $p->employee->designation->name ?? 'N/A' }}</td>
                        <td>{{ optional($p->employee->joining_date)->format('Y-m-d') }}</td>
                        <td>{{ number_format($p->basic_salary, 2) }}</td>
                        
                        <!-- Status bg-status-->
                        <td class="bg-status">{{ $p->present_days }}</td> <!-- P -->
                        <td class="bg-status">{{ $p->absent_days }}</td> <!-- A -->
                        <td class="bg-status">{{ $p->weekend_days }}</td> <!-- W -->
                        <td class="bg-status">{{ $p->holiday_days }}</td> <!-- H -->
                        <td class="bg-status">0.00</td> <!-- E.P -->
                        <td class="bg-status">0</td>    <!-- ABA -->
                        
                        <!-- Leave bg-leave -->
                        <td class="bg-leave">{{ $p->leave_days }}</td> <!-- E / Earned -->
                        <td class="bg-leave">0.00</td> <!-- S / Sick -->
                        
                        <!-- Late bg-late -->
                        <td class="bg-late">{{ $p->late_days }}</td>
                        <td class="bg-late">{{ floor($p->late_days / 3) }}</td>
                        <td class="bg-late">{{ number_format($p->late_deduction, 2) }}</td>
                        
                        <!-- Deduction bg-deduction -->
                        <td class="bg-deduction">{{ number_format($p->absent_deduction, 2) }}</td>
                        <td class="bg-deduction">0.00</td>
                        <td class="bg-deduction">{{ number_format($p->advance_salary_deduction, 2) }}</td>
                        <td class="bg-deduction">0</td>
                        
                        <!-- Totals -->
                        <td>{{ number_format($p->net_salary, 2) }}</td>
                        <td>0</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td class="bg-net">{{ number_format($p->net_salary, 2) }}</td>
                        <td>{{ $p->employee->bank_account ?? 'Cash/None' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="23">No processed payload data for this month.</td>
                    </tr>
                @endforelse
                
                @if(count($payrolls) > 0)
                <tr style="font-weight: bold; background-color: #f9fafb;">
                    <td colspan="4" style="text-align: right;">Total:</td>
                    <td>{{ number_format($gtSalary, 2) }}</td>
                    <td colspan="8"></td>
                    <td></td>
                    <td></td>
                    <td>{{ number_format($gtLateDed, 2) }}</td>
                    <td>{{ number_format($gtAbseDed, 2) }}</td>
                    <td></td>
                    <td>{{ number_format($gtAdvDed, 2) }}</td>
                    <td>{{ $gtLunch }}</td>
                    <td>{{ number_format($gtNet, 2) }}</td>
                    <td>0</td>
                    <td></td>
                    <td>{{ number_format($gtDaSal, 2) }}</td>
                    <td class="bg-net">{{ number_format($gtGrandNet, 2) }}</td>
                    <td></td>
                </tr>
                <tr style="font-weight: bold; background-color: #f3f4f6;">
                    <td colspan="4" style="text-align: right;">Grand Total:</td>
                    <td>{{ number_format($gtSalary, 2) }}</td>
                    <td colspan="8"></td>
                    <td></td>
                    <td></td>
                    <td>{{ number_format($gtLateDed, 2) }}</td>
                    <td>{{ number_format($gtAbseDed, 2) }}</td>
                    <td></td>
                    <td>{{ number_format($gtAdvDed, 2) }}</td>
                    <td>{{ $gtLunch }}</td>
                    <td>{{ number_format($gtNet, 2) }}</td>
                    <td>0</td>
                    <td></td>
                    <td>{{ number_format($gtDaSal, 2) }}</td>
                    <td class="bg-net">{{ number_format($gtGrandNet, 2) }}</td>
                    <td></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- Signatures -->
    <div style="display: flex; justify-content: space-between; margin-top: 80px; color: black; font-weight: bold; font-size: 14px;">
        <div>Prepared By</div>
        <div>Confirmed By</div>
        <div>Approved By (Managing Director)</div>
    </div>
</div>
