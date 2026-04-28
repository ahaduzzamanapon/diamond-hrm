<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advance_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->date('received_date');
            $table->string('start_deduct_month'); // YYYY-MM
            $table->integer('installment_count');
            $table->text('reason')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'completed'])->default('pending');
            // Adding a reviewer/approver ID conceptually
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_salaries');
    }
};
