<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('salary_month'); // Format: YYYY-MM
            $table->decimal('basic_salary', 10, 2)->default(0);
            
            // Allowances
            $table->decimal('house_rent', 10, 2)->default(0);
            $table->decimal('medical', 10, 2)->default(0);
            $table->decimal('transport', 10, 2)->default(0);
            $table->decimal('other_allowance', 10, 2)->default(0);
            
            // Deductions
            $table->decimal('absent_deduction', 10, 2)->default(0);
            $table->decimal('late_deduction', 10, 2)->default(0);
            $table->decimal('advance_salary_deduction', 10, 2)->default(0);
            $table->decimal('tax_deduction', 10, 2)->default(0);
            
            $table->decimal('gross_salary', 10, 2)->default(0);
            $table->decimal('total_deduction', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            
            $table->string('status')->default('draft'); // draft, processed, final
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
