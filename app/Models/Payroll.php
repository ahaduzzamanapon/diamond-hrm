<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'salary_month',
        'present_days',
        'absent_days',
        'late_days',
        'leave_days',
        'holiday_days',
        'weekend_days',
        'paid_days',
        'basic_salary',
        'house_rent',
        'medical',
        'transport',
        'other_allowance',
        'late_deduction',
        'absent_deduction',
        'advance_salary_deduction',
        'tax_deduction',
        'gross_salary',
        'total_deduction',
        'net_salary',
        'status',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
