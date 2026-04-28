<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = \App\Models\BiometricLog::orderBy('punch_time')->get();
$done = [];
foreach($logs as $log) {
    if($log->employee_id) {
        $key = $log->employee_id . '_' . $log->punch_time->format('Y-m-d');
        if(!isset($done[$key])) {
            $log->update(['punch_type' => 'in']);
            $done[$key] = true;
        } else {
            $log->update(['punch_type' => 'out']);
        }
    } else {
        $type = (int)$log->punch_time->format('H') < 14 ? 'in' : 'out';
        $log->update(['punch_type' => $type]);
    }
}
echo "Fixed in/out states for " . $logs->count() . " records.\n";
