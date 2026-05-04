<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Track which device & branch each punch (IN / OUT) came from
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Device serial (nullable — manual entries won't have it)
            $table->string('in_device_serial', 50)->nullable()->after('in_time');
            $table->string('out_device_serial', 50)->nullable()->after('out_time');

            // Branch where the punch happened (from device.branch_id)
            $table->unsignedBigInteger('in_branch_id')->nullable()->after('in_device_serial');
            $table->unsignedBigInteger('out_branch_id')->nullable()->after('out_device_serial');

            // Soft foreign key indexes (no constraint — device may be deleted)
            $table->index('in_branch_id',  'att_in_branch_idx');
            $table->index('out_branch_id', 'att_out_branch_idx');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('att_in_branch_idx');
            $table->dropIndex('att_out_branch_idx');
            $table->dropColumn(['in_device_serial', 'in_branch_id', 'out_device_serial', 'out_branch_id']);
        });
    }
};
