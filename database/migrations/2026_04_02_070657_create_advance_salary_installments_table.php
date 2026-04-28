<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advance_salary_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advance_salary_id')->constrained()->onDelete('cascade');
            $table->integer('installment_no');
            $table->string('deduct_month'); // YYYY-MM
            $table->decimal('amount', 10, 2);
            $table->boolean('is_deducted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_salary_installments');
    }
};
