<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('electric_v1_unit_exclusions')) {
            Schema::create('electric_v1_unit_exclusions', function (Blueprint $table) {
                $table->id();
                $table->string('unit_id', 80)->unique();
                $table->string('reason_code', 80)->default('NON_RESIDENT_NO_V1_READING');
                $table->text('reason')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['is_active', 'unit_id'], 'ev1_unit_exclusions_active_unit_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('electric_v1_unit_exclusions');
    }
};
