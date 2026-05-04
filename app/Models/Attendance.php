<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id','date',
        'in_time','in_device_serial','in_branch_id',
        'out_time','out_device_serial','out_branch_id',
        'working_minutes','late_minutes','early_out_minutes','overtime_minutes',
        'status','source','note','entered_by',
    ];
    protected $casts = ['date' => 'date'];

    public function employee()  { return $this->belongsTo(Employee::class); }
    public function enteredBy() { return $this->belongsTo(\App\Models\User::class, 'entered_by'); }
    public function inBranch()  { return $this->belongsTo(Branch::class, 'in_branch_id'); }
    public function outBranch() { return $this->belongsTo(Branch::class, 'out_branch_id'); }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'present'  => '<span class="badge badge-success">Present</span>',
            'absent'   => '<span class="badge badge-danger">Absent</span>',
            'late'     => '<span class="badge badge-warning">Late</span>',
            'half_day' => '<span class="badge badge-info">Half Day</span>',
            'holiday'  => '<span class="badge badge-secondary">Holiday</span>',
            'weekend'  => '<span class="badge badge-secondary">Weekend</span>',
            'leave'    => '<span class="badge badge-purple">Leave</span>',
            default    => '<span class="badge">-</span>',
        };
    }

    /**
     * Is the employee punching across branches?
     * true = IN and OUT happened at different branches.
     */
    public function getCrossBranchAttribute(): bool
    {
        return $this->in_branch_id && $this->out_branch_id
            && $this->in_branch_id !== $this->out_branch_id;
    }
}
