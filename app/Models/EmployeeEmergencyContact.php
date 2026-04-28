<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EmployeeEmergencyContact extends Model
{
    protected $fillable = ['employee_id','name','relation','phone','address'];
    public function employee() { return $this->belongsTo(Employee::class); }
}
