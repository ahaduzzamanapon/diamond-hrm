<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BiometricLog extends Model
{
    protected $fillable = ['device_serial','biometric_user_id','employee_id','punch_time','punch_type','verify_type','processed'];
    protected $casts = ['punch_time'=>'datetime','processed'=>'boolean'];
    public function employee() { return $this->belongsTo(Employee::class); }
    public function device()   { return $this->belongsTo(BiometricDevice::class, 'device_serial', 'serial_number'); }
}
