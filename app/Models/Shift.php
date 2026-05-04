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
        // BUG-107 & BUG-116 FIX: Use Carbon for robust parsing, handle overnight shifts
        $start = \Carbon\Carbon::parse($this->start_time);
        $end   = \Carbon\Carbon::parse($this->end_time);
        $mins  = $end->diffInMinutes($start);
        // If end < start, it's an overnight shift — add 24h
        if ($end->lt($start)) $mins += 1440;
        return max(0, $mins - ($this->break_minutes ?? 0));
    }
}
