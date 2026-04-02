<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_child_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('child_name');
            $table->string('school_name')->nullable();
            $table->string('class_grade')->nullable();
            $table->boolean('school_going')->default(false);
            $table->boolean('van_using')->default(false);
            $table->date('transport_join_date')->nullable();
            $table->date('transport_leave_date')->nullable();
            $table->string('default_route_label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'sort_order']);
        });

        Schema::create('transport_child_month_usage', function (Blueprint $table) {
            $table->id();
            $table->string('month_cycle', 7);
            $table->foreignId('child_profile_id')->constrained('family_child_profiles')->cascadeOnDelete();
            $table->string('usage_status')->nullable();
            $table->date('usage_from_date')->nullable();
            $table->date('usage_to_date')->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained('transport_vehicles')->nullOnDelete();
            $table->string('route_label')->nullable();
            $table->decimal('charge_amount', 14, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['month_cycle', 'child_profile_id']);
            $table->index(['month_cycle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_child_month_usage');
        Schema::dropIfExists('family_child_profiles');
    }
};
