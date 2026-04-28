<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = ['name','code','days_per_year','carry_forward','is_paid','color','is_active'];
    protected $casts = ['carry_forward'=>'boolean','is_paid'=>'boolean','is_active'=>'boolean'];
    public function applications() { return $this->hasMany(LeaveApplication::class); }
}
