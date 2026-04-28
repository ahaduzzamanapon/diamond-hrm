<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('serial_number')->unique();
            $table->string('ip_address')->nullable();
            $table->string('model')->nullable();
            $table->timestamp('last_online')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('biometric_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_serial');
            $table->string('biometric_user_id');
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('punch_time');
            $table->enum('punch_type', ['in', 'out', 'unknown'])->default('unknown');
            $table->integer('verify_type')->default(0); // 0=finger, 15=face
            $table->boolean('processed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_logs');
        Schema::dropIfExists('biometric_devices');
    }
};
