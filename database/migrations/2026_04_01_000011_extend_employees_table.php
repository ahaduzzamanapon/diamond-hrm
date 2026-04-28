<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Extend employees table with all form fields
        Schema::table('employees', function (Blueprint $table) {
            $table->string('first_name')->after('employee_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('contact_number')->nullable()->after('email');
            $table->string('username')->unique()->nullable()->after('contact_number');
            $table->integer('probation_months')->default(0)->after('joining_date');
            $table->enum('employee_type', ['employee', 'lead'])->default('employee')->after('probation_months');
            $table->foreignId('team_leader_id')->nullable()->after('employee_type')->constrained('employees')->nullOnDelete();
            $table->foreignId('leave_type_id')->nullable()->after('team_leader_id')->constrained('leave_types')->nullOnDelete();
            $table->string('permanent_address')->nullable()->after('address');
            $table->text('remark')->nullable()->after('permanent_address');
            $table->string('note_file')->nullable()->after('remark');
        });

        // Social Networking
        Schema::create('employee_socials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });

        // Documents
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['nid','passport','photo','education','experience','other'])->default('other');
            $table->string('title');
            $table->string('file');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Qualifications
        Schema::create('employee_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('degree');
            $table->string('institution');
            $table->string('major')->nullable();
            $table->year('from_year')->nullable();
            $table->year('to_year')->nullable();
            $table->string('result')->nullable();
            $table->timestamps();
        });

        // Contracts
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['permanent', 'contract', 'intern', 'probation'])->default('permanent');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('salary', 12, 2)->default(0);
            $table->string('file')->nullable();
            $table->text('terms')->nullable();
            $table->enum('status', ['active', 'expired', 'terminated'])->default('active');
            $table->timestamps();
        });

        // Emergency contacts (separate table for multiple contacts)
        Schema::create('employee_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('relation', ['father','mother','spouse','sibling','friend','colleague','other'])->default('other');
            $table->string('phone');
            $table->string('address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_emergency_contacts');
        Schema::dropIfExists('employee_contracts');
        Schema::dropIfExists('employee_qualifications');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employee_socials');
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['team_leader_id']);
            $table->dropForeign(['leave_type_id']);
            $table->dropColumn([
                'first_name','last_name','contact_number','username','probation_months',
                'employee_type','team_leader_id','leave_type_id','permanent_address','remark','note_file'
            ]);
        });
    }
};
