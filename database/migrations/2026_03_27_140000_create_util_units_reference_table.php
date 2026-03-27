<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('util_units_reference')) {
            return;
        }

        Schema::create('util_units_reference', function (Blueprint $table) {
            $table->id();
            $table->string('unit_id')->unique();
            $table->string('colony_type')->nullable();
            $table->string('block_name')->nullable();
            $table->string('room_no')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('util_units_reference');
    }
};
