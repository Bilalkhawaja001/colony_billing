<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_code', 50)->unique();
            $table->string('vehicle_name', 150);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('transport_rent_entries', function (Blueprint $table) {
            $table->id();
            $table->string('month_cycle', 7);
            $table->foreignId('vehicle_id')->constrained('transport_vehicles')->cascadeOnDelete();
            $table->decimal('rent_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['month_cycle', 'vehicle_id']);
            $table->index(['month_cycle']);
        });

        Schema::create('transport_fuel_entries', function (Blueprint $table) {
            $table->id();
            $table->string('month_cycle', 7);
            $table->date('entry_date');
            $table->foreignId('vehicle_id')->constrained('transport_vehicles')->cascadeOnDelete();
            $table->decimal('fuel_liters', 14, 3)->default(0);
            $table->decimal('fuel_price', 14, 2)->default(0);
            $table->decimal('fuel_cost', 14, 2)->default(0);
            $table->string('slip_ref', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['month_cycle', 'vehicle_id']);
        });

        Schema::create('transport_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('month_cycle', 7);
            $table->foreignId('vehicle_id')->nullable()->constrained('transport_vehicles')->nullOnDelete();
            $table->enum('direction', ['plus', 'minus'])->default('plus');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('reason', 255);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['month_cycle', 'vehicle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_adjustments');
        Schema::dropIfExists('transport_fuel_entries');
        Schema::dropIfExists('transport_rent_entries');
        Schema::dropIfExists('transport_vehicles');
    }
};
