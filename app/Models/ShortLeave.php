<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ShortLeave extends Model
{
    protected $fillable = [
        'employee_id', 'date', 'out_time', 'in_time',
        'duration_minutes', 'reason', 'note', 'remarks',
        'status', 'entered_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'date'        => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee()   { return $this->belongsTo(Employee::class); }
    public function enteredBy()  { return $this->belongsTo(User::class, 'entered_by'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }

    /**
     * Auto-calculate duration_minutes from out_time & in_time.
     */
    public static function calcDuration(string $outTime, ?string $inTime): int
    {
        if (!$inTime) return 0;
        $out = Carbon::createFromTimeString($outTime);
        $in  = Carbon::createFromTimeString($inTime);
        return max(0, $out->diffInMinutes($in));
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'approved' => '<span class="badge badge-success">Approved</span>',
            'rejected' => '<span class="badge badge-danger">Rejected</span>',
            default    => '<span class="badge badge-warning">Pending</span>',
        };
    }

    public function getDurationFormattedAttribute(): string
    {
        $m = $this->duration_minutes;
        if ($m <= 0) return '—';
        $h = intdiv($m, 60);
        $min = $m % 60;
        return $h > 0 ? "{$h}h {$min}m" : "{$min}m";
    }
}
