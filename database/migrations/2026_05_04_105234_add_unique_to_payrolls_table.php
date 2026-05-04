<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// BUG-126 FIX: Add unique constraint to payrolls (one record per employee per month)
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Remove any existing duplicates first
            \DB::statement('
                DELETE p1 FROM payrolls p1
                INNER JOIN payrolls p2
                WHERE p1.id > p2.id
                  AND p1.employee_id = p2.employee_id
                  AND p1.salary_month = p2.salary_month
            ');
            $table->unique(['employee_id', 'salary_month'], 'unique_employee_month');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropUnique('unique_employee_month');
        });
    }
};
