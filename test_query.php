<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$q = \App\Models\BiometricLog::whereYear('punch_time', '2026')->whereMonth('punch_time', '04');
echo "Count for 2026-04: " . $q->count() . "\n";
$first = $q->first();
if ($first) {
    echo "First punch_time: " . $first->punch_time . "\n";
    echo "Employee ID: " . $first->employee_id . "\n";
    echo "Device Branch ID: " . ($first->device ? $first->device->branch_id : 'null') . "\n";
} else {
    echo "No records found matching April 2026!\n";
}

// Emulate user query logic
$user = \App\Models\User::first();
echo "Super Admin View All Branches Permission: " . ($user->hasPermissionTo('view_all_branches') ? 'true' : 'false') . "\n";

$fullQuery = \App\Models\BiometricLog::with(['employee.branch', 'device'])
    ->when(!$user->hasPermissionTo('view_all_branches'), function($q) use ($user) {
        $q->where(function($query) use ($user) {
            $query->whereHas('employee', fn($e) => $e->where('branch_id', $user->branch_id))
                  ->orWhere(function($sub) use ($user) {
                      $sub->whereNull('employee_id')
                          ->whereHas('device', fn($d) => $d->where('branch_id', $user->branch_id));
                  });
        });
    })
    ->whereYear('punch_time', '2026')->whereMonth('punch_time', '04');
    
echo "Full Controller Query Count: " . $fullQuery->count() . "\n";
