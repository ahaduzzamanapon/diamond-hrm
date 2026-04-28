<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EmployeeContract extends Model
{
    protected $fillable = ['employee_id','type','start_date','end_date','salary','file','terms','status'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','salary'=>'decimal:2'];
    public function employee() { return $this->belongsTo(Employee::class); }
}
