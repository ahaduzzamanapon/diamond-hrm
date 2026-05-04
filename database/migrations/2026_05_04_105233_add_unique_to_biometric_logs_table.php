<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// BUG-124 FIX: Add unique index to prevent duplicate biometric log entries
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biometric_logs', function (Blueprint $table) {
            // Remove duplicates before adding unique index
            \DB::statement('
                DELETE b1 FROM biometric_logs b1
                INNER JOIN biometric_logs b2
                WHERE b1.id > b2.id
                  AND b1.device_serial = b2.device_serial
                  AND b1.biometric_user_id = b2.biometric_user_id
                  AND b1.punch_time = b2.punch_time
            ');
            $table->unique(['device_serial', 'biometric_user_id', 'punch_time'], 'unique_punch');
        });
    }

    public function down(): void
    {
        Schema::table('biometric_logs', function (Blueprint $table) {
            $table->dropUnique('unique_punch');
        });
    }
};
