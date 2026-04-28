<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;
    protected $fillable = ['branch_id','name','code','description','is_active'];

    public function branch()      { return $this->belongsTo(Branch::class); }
    public function designations(){ return $this->hasMany(Designation::class); }
    public function employees()   { return $this->hasMany(Employee::class); }
}
