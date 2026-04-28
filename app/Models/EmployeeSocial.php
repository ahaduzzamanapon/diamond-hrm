<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSocial extends Model
{
    protected $fillable = ['employee_id','facebook','twitter','linkedin','instagram','youtube','website'];
    public function employee() { return $this->belongsTo(Employee::class); }
}

// ---------------------------------------------------------------------------

