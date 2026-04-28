<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_units', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Pieces, Boxes, Kilograms
            $table->string('short_name')->nullable(); // e.g. Pcs, Box, Kg
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_units');
    }
};
