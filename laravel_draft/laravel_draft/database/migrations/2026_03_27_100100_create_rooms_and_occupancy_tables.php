<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('util_unit_room_snapshot', function (Blueprint $table) {
            $table->id();
            $table->string('month_cycle', 7);
            $table->string('unit_id');
            $table->string('category');
            $table->string('block_floor')->nullable();
            $table->string('room_no');
            $table->timestamps();

            $table->unique(['month_cycle', 'unit_id', 'room_no']);
            $table->index(['month_cycle', 'unit_id']);
        });

        Schema::create('util_occupancy_monthly', function (Blueprint $table) {
            $table->id();
            $table->string('month_cycle', 7);
            $table->string('category');
            $table->string('block_floor')->nullable();
            $table->string('room_no');
            $table->string('unit_id');
            $table->string('employee_id');
            $table->integer('active_days')->default(0);
            $table->timestamps();

            $table->unique(['month_cycle', 'employee_id']);
            $table->index(['month_cycle', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('util_occupancy_monthly');
        Schema::dropIfExists('util_unit_room_snapshot');
    }
};
