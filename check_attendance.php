<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BiometricLog;
use App\Models\BiometricDevice;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

echo "=== Biometric Device Status ===\n";
BiometricDevice::all()->each(fn($d) =>
    printf("  id=%d name=%-20s serial=%-15s branch_id=%d active=%s\n",
        $d->id, $d->name, $d->serial_number, $d->branch_id, $d->is_active ? 'yes' : 'no')
);

echo "\n=== BiometricLog Summary ===\n";
$total      = BiometricLog::count();
$processed  = BiometricLog::where('processed', true)->count();
$unprocessed= BiometricLog::where('processed', false)->count();
$noEmp      = BiometricLog::whereNull('employee_id')->count();
echo "  Total logs   : {$total}\n";
echo "  Processed    : {$processed}\n";
echo "  Unprocessed  : {$unprocessed}\n";
echo "  No emp mapped: {$noEmp}\n";

echo "\n=== Employee biometric_user_id mapping ===\n";
Employee::where('status','active')->get()->each(fn($e) =>
    printf("  %-25s bio_uid=%-10s branch_id=%d\n", $e->name, $e->biometric_user_id ?? 'NULL', $e->branch_id)
);

echo "\n=== Logs for Employee #2 ===\n";
$logs = BiometricLog::where('employee_id', 2)->orderByDesc('punch_time')->limit(10)->get();
if ($logs->isEmpty()) {
    // Try by bio user id
    $emp2 = Employee::find(2);
    echo "  No logs by employee_id=2\n";
    echo "  Employee biometric_user_id = " . ($emp2?->biometric_user_id ?? 'NULL') . "\n";
    if ($emp2?->biometric_user_id) {
        $logsByUid = BiometricLog::where('biometric_user_id', $emp2->biometric_user_id)->limit(5)->get();
        echo "  Logs by biometric_user_id: " . $logsByUid->count() . "\n";
        $logsByUid->each(fn($l) => print("    {$l->punch_time} processed={$l->processed} emp_id={$l->employee_id}\n"));
    }
} else {
    $logs->each(fn($l) =>
        printf("  %s  type=%-3s  processed=%s  emp=%s\n",
            $l->punch_time, $l->punch_type, $l->processed ? 'yes':'no', $l->employee_id ?? '—')
    );
}

echo "\n=== Latest unprocessed logs (any employee) ===\n";
BiometricLog::where('processed', false)->orderByDesc('punch_time')->limit(5)->get()
    ->each(fn($l) => printf("  %s bio_uid=%-8s emp_id=%s device=%s\n",
        $l->punch_time, $l->biometric_user_id, $l->employee_id ?? 'NULL', $l->device_serial));
