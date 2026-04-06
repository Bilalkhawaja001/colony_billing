<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('util_meter_unit', function (Blueprint $table) {
            $table->string('meter_id')->primary();
            $table->string('unit_id');
            $table->string('meter_type')->nullable();
            $table->integer('is_active')->default(1);
            $table->timestamps();

            $table->index(['unit_id', 'is_active']);
        });

        Schema::create('util_meter_readings', function (Blueprint $table) {
            $table->id();
            $table->string('meter_id');
            $table->string('unit_id');
            $table->date('reading_date');
            $table->decimal('reading_value', 12, 3);
            $table->timestamps();

            $table->unique(['meter_id', 'reading_date']);
            $table->index(['unit_id', 'reading_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('util_meter_readings');
        Schema::dropIfExists('util_meter_unit');
    }
};
