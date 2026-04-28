<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->integer('present_days')->default(0)->after('salary_month');
            $table->integer('absent_days')->default(0)->after('present_days');
            $table->integer('late_days')->default(0)->after('absent_days');
            $table->integer('leave_days')->default(0)->after('late_days');
            $table->integer('holiday_days')->default(0)->after('leave_days');
            $table->integer('weekend_days')->default(0)->after('holiday_days');
            $table->decimal('paid_days', 5, 2)->default(0)->after('weekend_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'present_days', 'absent_days', 'late_days', 
                'leave_days', 'holiday_days', 'weekend_days', 'paid_days'
            ]);
        });
    }
};
