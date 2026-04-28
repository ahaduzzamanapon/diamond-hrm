<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BiometricDevice extends Model
{
    protected $fillable = ['branch_id','name','serial_number','ip_address','model','last_online','is_active'];
    protected $casts = ['last_online'=>'datetime','is_active'=>'boolean'];
    public function branch() { return $this->belongsTo(Branch::class); }
    public function logs()   { return $this->hasMany(BiometricLog::class,'device_serial','serial_number'); }

    public function getIsOnlineAttribute(): bool
    {
        return $this->last_online && $this->last_online->diffInMinutes(now()) < 5;
    }
}
