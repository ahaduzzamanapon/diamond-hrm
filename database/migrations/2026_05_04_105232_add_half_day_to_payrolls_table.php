<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// BUG-110 FIX: Add half_day tracking columns to payrolls table
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->unsignedSmallInteger('half_days')->default(0)->after('late_days');
            $table->decimal('half_day_deduction', 10, 2)->default(0)->after('absent_deduction');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['half_days', 'half_day_deduction']);
        });
    }
};
