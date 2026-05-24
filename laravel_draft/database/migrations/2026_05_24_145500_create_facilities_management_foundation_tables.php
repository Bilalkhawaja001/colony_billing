<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('facility_registries')) {
            Schema::create('facility_registries', function (Blueprint $table) {
                $table->id();
                $table->string('section', 120)->nullable()->index();
                $table->string('area', 160)->nullable()->index();
                $table->string('specific_location', 255)->nullable();
                $table->string('facility_code', 80)->unique();
                $table->string('facility_name', 255);
                $table->string('facility_type', 80)->index();
                $table->string('status', 40)->default('OPEN')->index();
                $table->string('condition', 40)->nullable()->index();
                $table->boolean('is_active')->default(true)->index();
                $table->text('notes')->nullable();
                $table->string('created_by', 120)->nullable();
                $table->string('updated_by', 120)->nullable();
                $table->timestamps();

                $table->index(['section', 'area'], 'fr_section_area_idx');
                $table->index(['facility_type', 'is_active'], 'fr_type_active_idx');
            });
        }

        if (!Schema::hasTable('facility_component_types')) {
            Schema::create('facility_component_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('facility_components')) {
            Schema::create('facility_components', function (Blueprint $table) {
                $table->id();
                $table->foreignId('facility_id')->constrained('facility_registries')->cascadeOnDelete();
                $table->string('component_type', 100)->index();
                $table->string('component_name', 160)->nullable();
                $table->decimal('quantity', 10, 2)->default(1);
                $table->string('condition', 40)->nullable()->index();
                $table->string('status', 40)->default('ACTIVE')->index();
                $table->boolean('is_active')->default(true)->index();
                $table->text('notes')->nullable();
                $table->string('created_by', 120)->nullable();
                $table->string('updated_by', 120)->nullable();
                $table->timestamps();

                $table->index(['facility_id', 'component_type'], 'fc_facility_type_idx');
            });
        }

        if (!Schema::hasTable('facility_work_categories')) {
            Schema::create('facility_work_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120)->unique();
                $table->string('category_group', 60)->default('Maintenance')->index();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('facility_work_orders')) {
            Schema::create('facility_work_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('facility_id')->nullable()->constrained('facility_registries')->nullOnDelete();
                $table->foreignId('facility_component_id')->nullable()->constrained('facility_components')->nullOnDelete();
                $table->foreignId('facility_work_category_id')->nullable()->constrained('facility_work_categories')->nullOnDelete();
                $table->string('work_order_no', 80)->nullable()->unique();
                $table->string('title', 255);
                $table->string('priority', 40)->default('NORMAL')->index();
                $table->string('status', 40)->default('OPEN')->index();
                $table->date('reported_on')->nullable()->index();
                $table->date('target_date')->nullable()->index();
                $table->date('completed_on')->nullable();
                $table->date('verified_on')->nullable();
                $table->text('material_required')->nullable();
                $table->text('material_remarks')->nullable();
                $table->decimal('estimated_cost', 12, 2)->nullable();
                $table->decimal('actual_cost', 12, 2)->nullable();
                $table->text('notes')->nullable();
                $table->string('created_by', 120)->nullable();
                $table->string('updated_by', 120)->nullable();
                $table->timestamps();

                $table->index(['status', 'priority'], 'fwo_status_priority_idx');
                $table->index(['facility_id', 'status'], 'fwo_facility_status_idx');
            });
        }

        if (!Schema::hasTable('facility_daily_services')) {
            Schema::create('facility_daily_services', function (Blueprint $table) {
                $table->id();
                $table->foreignId('facility_id')->nullable()->constrained('facility_registries')->nullOnDelete();
                $table->string('service_type', 100)->index();
                $table->date('service_date')->index();
                $table->string('status', 40)->default('PENDING')->index();
                $table->string('performed_by', 160)->nullable();
                $table->string('verified_by', 160)->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['service_date', 'status'], 'fds_date_status_idx');
            });
        }

        $componentTypes = [
            'Commode', 'Flush Tank', 'Wash Basin', 'Faucet / Tap', 'Shower', 'Mirror', 'Exhaust Fan',
            'Door', 'Drain', 'Light', 'Water Line', 'Geyser', 'Other Component',
        ];
        foreach ($componentTypes as $index => $name) {
            DB::table('facility_component_types')->updateOrInsert(
                ['name' => $name],
                ['is_active' => 1, 'sort_order' => $index + 1, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $categories = [
            ['Plumbing', 'Maintenance'],
            ['Electrical', 'Maintenance'],
            ['Civil Works', 'Maintenance'],
            ['HVAC', 'Maintenance'],
            ['Water Supply / RO / Geyser', 'Maintenance'],
            ['Washroom & Sanitation', 'Maintenance'],
            ['Market Maintenance', 'Maintenance'],
            ['Masjid Maintenance', 'Maintenance'],
            ['Colony / Guest House / Office', 'Maintenance'],
            ['Garden / Fountain / Common Area', 'Maintenance'],
            ['Fire & Safety Facility Works', 'Maintenance'],
            ['Daily Cleaning', 'Routine Service'],
            ['Pest Control / Fumigation', 'Routine Service'],
        ];
        foreach ($categories as $index => [$name, $group]) {
            DB::table('facility_work_categories')->updateOrInsert(
                ['name' => $name],
                ['category_group' => $group, 'is_active' => 1, 'sort_order' => $index + 1, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_daily_services');
        Schema::dropIfExists('facility_work_orders');
        Schema::dropIfExists('facility_work_categories');
        Schema::dropIfExists('facility_components');
        Schema::dropIfExists('facility_component_types');
        Schema::dropIfExists('facility_registries');
    }
};
