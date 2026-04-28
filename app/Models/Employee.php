<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id','user_id','branch_id','department_id','designation_id','shift_id',
        'first_name','last_name','name','email','phone','contact_number','username',
        'photo','gender','date_of_birth','joining_date','leaving_date',
        'probation_months','employee_type','team_leader_id','leave_type_id',
        'nid','blood_group','address','permanent_address','remark','note_file',
        'emergency_contact_name','emergency_contact_phone',
        'basic_salary','house_rent_allowance','medical_allowance','transport_allowance',
        'bank_name','bank_account','biometric_user_id','status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date'  => 'date',
        'leaving_date'  => 'date',
        'basic_salary'  => 'decimal:2',
    ];

    // Auto-set name from first+last
    protected static function booted(): void
    {
        static::saving(function ($employee) {
            $employee->name = trim($employee->first_name . ' ' . $employee->last_name);
        });
    }

    public function branch(): BelongsTo       { return $this->belongsTo(Branch::class); }
    public function department(): BelongsTo   { return $this->belongsTo(Department::class); }
    public function designation(): BelongsTo  { return $this->belongsTo(Designation::class); }
    public function shift(): BelongsTo        { return $this->belongsTo(Shift::class); }
    public function user(): BelongsTo         { return $this->belongsTo(User::class); }
    public function teamLeader(): BelongsTo   { return $this->belongsTo(Employee::class, 'team_leader_id'); }
    public function leaveCategory(): BelongsTo { return $this->belongsTo(LeaveType::class, 'leave_type_id'); }

    public function social(): HasOne      { return $this->hasOne(EmployeeSocial::class); }
    public function documents(): HasMany  { return $this->hasMany(EmployeeDocument::class); }
    public function qualifications(): HasMany { return $this->hasMany(EmployeeQualification::class); }
    public function contracts(): HasMany  { return $this->hasMany(EmployeeContract::class); }
    public function emergencyContacts(): HasMany { return $this->hasMany(EmployeeEmergencyContact::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function leaves(): HasMany      { return $this->hasMany(LeaveApplication::class); }
    public function advanceSalaries(): HasMany { return $this->hasMany(AdvanceSalary::class); }
    public function payrolls(): HasMany { return $this->hasMany(Payroll::class); }
    public function transfers(): HasMany { return $this->hasMany(EmployeeTransfer::class)->orderBy('effective_date'); }

    /**
     * Get the Shift that was active for this employee on a given date.
     * Respects transfer history — e.g. if transferred mid-month, uses the
     * shift they were on that specific day, not their current shift.
     */
    public function getShiftForDate(string $date): ?Shift
    {
        $transfers = $this->transfers()->with('fromShift','toShift')->orderBy('effective_date')->get();

        if ($transfers->isEmpty()) {
            return $this->shift;
        }

        $d = Carbon::parse($date);
        $first = $transfers->first();

        // Before the first ever transfer — return that transfer's from_shift
        if ($d->lt(Carbon::parse($first->effective_date))) {
            return $first->fromShift;
        }

        // Find the last transfer whose effective_date is on or before $date
        $applicable = $transfers->filter(
            fn($t) => Carbon::parse($t->effective_date)->lte($d)
        )->last();

        return $applicable?->toShift ?? $this->shift;
    }

    /**
     * Get the Branch that was active for this employee on a given date.
     */
    public function getBranchForDate(string $date): ?Branch
    {
        $transfers = $this->transfers()->with('fromBranch','toBranch')->orderBy('effective_date')->get();

        if ($transfers->isEmpty()) {
            return $this->branch;
        }

        $d = Carbon::parse($date);
        $first = $transfers->first();

        if ($d->lt(Carbon::parse($first->effective_date))) {
            return $first->fromBranch;
        }

        $applicable = $transfers->filter(
            fn($t) => Carbon::parse($t->effective_date)->lte($d)
        )->last();

        return $applicable?->toBranch ?? $this->branch;
    }


    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getPhotoUrlAttribute(): string
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff&size=128';
    }

    public function getGrossSalaryAttribute(): float
    {
        return $this->basic_salary + $this->house_rent_allowance
             + $this->medical_allowance + $this->transport_allowance;
    }

    public function getDailyRateAttribute(): float
    {
        return $this->basic_salary > 0 ? round($this->basic_salary / 26, 2) : 0;
    }

    public function getHourlyRateAttribute(): float
    {
        return $this->basic_salary > 0 ? round($this->basic_salary / 26 / 8, 2) : 0;
    }

    // Scope: filter by branch for role-based isolation
    public function scopeForUser($query, $user)
    {
        if ($user->hasPermissionTo('view_all_branches')) {
            return $query;
        }
        return $query->where('branch_id', $user->branch_id);
    }
}
