<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceSalaryInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'advance_salary_id',
        'installment_no',
        'deduct_month',
        'amount',
        'is_deducted',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_deducted' => 'boolean',
    ];

    public function advanceSalary(): BelongsTo
    {
        return $this->belongsTo(AdvanceSalary::class);
    }
}
