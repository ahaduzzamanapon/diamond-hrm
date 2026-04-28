<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add extra_present status to attendances
        Schema::table('attendances', function (Blueprint $table) {
            // Change enum to include extra_present
            \DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent','late','half_day','holiday','weekend','leave','extra_present') DEFAULT 'absent'");
        });

        // Extra present approval requests
        Schema::create('extra_present_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('day_type', ['holiday', 'weekend']); // why they worked
            $table->string('holiday_name')->nullable();        // if holiday, its name
            $table->text('reason')->nullable();                // employee reason
            $table->decimal('extra_pay', 12, 2)->default(0);  // calculated pay
            // Branch Manager approval
            $table->enum('bm_status', ['pending','approved','rejected'])->default('pending');
            $table->foreignId('bm_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('bm_approved_at')->nullable();
            $table->text('bm_remarks')->nullable();
            // HR final approval
            $table->enum('hr_status', ['pending','approved','rejected'])->default('pending');
            $table->foreignId('hr_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hr_approved_at')->nullable();
            $table->text('hr_remarks')->nullable();
            // Final status (both must approve)
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->boolean('added_to_payroll')->default(false);
            $table->timestamps();
        });

        // Two-level leave approval columns
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->enum('bm_status', ['pending','approved','rejected'])->default('pending')->after('status');
            $table->foreignId('bm_approved_by')->nullable()->after('bm_status')->constrained('users')->nullOnDelete();
            $table->timestamp('bm_approved_at')->nullable()->after('bm_approved_by');
            $table->text('bm_remarks')->nullable()->after('bm_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropForeign(['bm_approved_by']);
            $table->dropColumn(['bm_status','bm_approved_by','bm_approved_at','bm_remarks']);
        });
        Schema::dropIfExists('extra_present_requests');
        \DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent','late','half_day','holiday','weekend','leave') DEFAULT 'absent'");
    }
};
