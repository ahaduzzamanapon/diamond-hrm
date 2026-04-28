<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name','start_time','end_time','grace_minutes','break_minutes',
        'sunday','monday','tuesday','wednesday','thursday','friday','saturday','is_active'
    ];
    protected $casts = [
        'sunday'=>'boolean','monday'=>'boolean','tuesday'=>'boolean',
        'wednesday'=>'boolean','thursday'=>'boolean','friday'=>'boolean','saturday'=>'boolean',
    ];

    public function employees() { return $this->hasMany(Employee::class); }

    public function getWorkingDaysAttribute(): array
    {
        $days = [];
        $map = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
        foreach ($map as $day) {
            if ($this->$day) $days[] = ucfirst($day);
        }
        return $days;
    }

    public function getWorkingMinutesAttribute(): int
    {
        [$sh,$sm] = explode(':', $this->start_time);
        [$eh,$em] = explode(':', $this->end_time);
        return ($eh * 60 + $em) - ($sh * 60 + $sm) - $this->break_minutes;
    }
}
