<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id','date','in_time','out_time','working_minutes',
        'late_minutes','early_out_minutes','overtime_minutes','status','source','note','entered_by'
    ];
    protected $casts = ['date'=>'date'];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function enteredBy() { return $this->belongsTo(\App\Models\User::class, 'entered_by'); }

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
}
