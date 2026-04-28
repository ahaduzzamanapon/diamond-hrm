<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EmployeeQualification extends Model
{
    protected $fillable = ['employee_id','degree','institution','major','from_year','to_year','result'];
    public function employee() { return $this->belongsTo(Employee::class); }
}
