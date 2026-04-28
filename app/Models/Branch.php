<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;
    protected $fillable = ['name','code','address','phone','email','logo','is_active'];

    public function departments() { return $this->hasMany(Department::class); }
    public function employees()   { return $this->hasMany(Employee::class); }
    public function users()       { return $this->hasMany(\App\Models\User::class); }
    public function holidays()    { return $this->hasMany(Holiday::class); }
    public function notices()     { return $this->hasMany(Notice::class); }
    public function biometricDevices() { return $this->hasMany(BiometricDevice::class); }
}
