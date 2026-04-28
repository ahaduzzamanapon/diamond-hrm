<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    protected $fillable = [
        'employee_id','leave_type_id','from_date','to_date','total_days',
        'reason','document','status','approved_by','remarks','approved_at',
        'bm_status','bm_approved_by','bm_approved_at','bm_remarks',
    ];
    protected $casts = [
        'from_date'=>'date','to_date'=>'date','approved_at'=>'datetime','bm_approved_at'=>'datetime',
    ];

    public function employee()  { return $this->belongsTo(Employee::class); }
    public function leaveType() { return $this->belongsTo(LeaveType::class); }
    public function approver()  { return $this->belongsTo(User::class,'approved_by'); }



}
