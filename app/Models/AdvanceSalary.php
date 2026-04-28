<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdvanceSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'amount',
        'received_date',
        'start_deduct_month',
        'installment_count',
        'reason',
        'status',
        'approved_by'
    ];

    protected $casts = [
        'received_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(AdvanceSalaryInstallment::class);
    }
}
