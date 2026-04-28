<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->integer('working_minutes')->nullable();
            $table->integer('late_minutes')->default(0);
            $table->integer('early_out_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'holiday', 'weekend', 'leave'])->default('absent');
            $table->enum('source', ['manual', 'biometric', 'import'])->default('manual');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
