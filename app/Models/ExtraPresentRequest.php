<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ExtraPresentRequest extends Model
{
    protected $fillable = [
        'employee_id','attendance_id','date','day_type','holiday_name','reason','extra_pay',
        'bm_status','bm_approved_by','bm_approved_at','bm_remarks',
        'hr_status','hr_approved_by','hr_approved_at','hr_remarks',
        'status','added_to_payroll',
    ];
    protected $casts = [
        'date'=>'date','bm_approved_at'=>'datetime','hr_approved_at'=>'datetime',
        'extra_pay'=>'decimal:2','added_to_payroll'=>'boolean',
    ];

    public function employee()   { return $this->belongsTo(Employee::class); }
    public function attendance() { return $this->belongsTo(Attendance::class); }
    public function bmApprover() { return $this->belongsTo(User::class,'bm_approved_by'); }
    public function hrApprover() { return $this->belongsTo(User::class,'hr_approved_by'); }

    /**
     * Approve by Branch Manager
     */
    public function approveBM(User $user, string $remarks = ''): void
    {
        $this->update([
            'bm_status'      => 'approved',
            'bm_approved_by' => $user->id,
            'bm_approved_at' => now(),
            'bm_remarks'     => $remarks,
        ]);
    }

    /**
     * Final approve by HR — only if BM already approved
     */
    public function approveHR(User $user, string $remarks = ''): bool
    {
        if ($this->bm_status !== 'approved') return false;

        $this->update([
            'hr_status'      => 'approved',
            'hr_approved_by' => $user->id,
            'hr_approved_at' => now(),
            'hr_remarks'     => $remarks,
            'status'         => 'approved',
        ]);

        // Update attendance status
        $this->attendance->update(['status' => 'extra_present']);

        return true;
    }

    public function reject(User $user, string $level, string $remarks = ''): void
    {
        $updates = ['status' => 'rejected', "{$level}_remarks" => $remarks];
        $updates["{$level}_status"] = 'rejected';
        $updates["{$level}_approved_by"] = $user->id;
        $this->update($updates);
    }
}
