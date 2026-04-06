<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('util_water_zone_monthly_input', function (Blueprint $table) {
            $table->id();
            $table->string('month_cycle', 7);
            $table->string('water_zone', 32);
            $table->decimal('raw_liters', 14, 2)->default(0);
            $table->decimal('common_use_liters', 14, 2)->default(0);
            $table->string('reason_code')->nullable();
            $table->text('notes')->nullable();
            $table->string('source_ref')->nullable();
            $table->timestamps();

            $table->unique(['month_cycle', 'water_zone']);
            $table->index(['month_cycle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('util_water_zone_monthly_input');
    }
};
