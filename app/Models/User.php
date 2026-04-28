<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = ['name','email','password','branch_id','avatar','is_active'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean'];

    public function branch(): BelongsTo  { return $this->belongsTo(Branch::class); }
    public function employee()           { return $this->hasOne(Employee::class); }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff&size=64';
    }

    // Helper: can user see a given branch_id?
    public function canSeeBranch(int $branchId): bool
    {
        if ($this->hasPermissionTo('view_all_branches')) return true;
        return $this->branch_id === $branchId;
    }
}
