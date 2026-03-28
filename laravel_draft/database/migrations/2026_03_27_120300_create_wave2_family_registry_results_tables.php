<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('family_details', function (Blueprint $table) {
            $table->id();
            $table->string('month_cycle');
            $table->string('company_id');
            $table->string('employee_name')->nullable();
            $table->string('unit_id')->nullable();
            $table->string('category')->nullable();
            $table->string('colony_type')->nullable();
            $table->string('block_floor')->nullable();
            $table->string('room_no')->nullable();
            $table->string('spouse_name')->nullable();
            $table->string('school_name')->nullable();
            $table->string('class_name')->nullable();
            $table->integer('age')->nullable();
            $table->integer('spouse_count')->default(0);
            $table->integer('children_count')->default(0);
            $table->integer('school_going_children')->default(0);
            $table->integer('van_using_children')->default(0);
            $table->integer('van_using_adults')->default(0);
            $table->integer('van_trips_per_day')->default(1);
            $table->string('deduction_mode')->default('None');
            $table->decimal('deduction_amount', 12, 2)->default(0);
            $table->string('effective_from')->nullable();
            $table->string('effective_to')->nullable();
            $table->text('remarks')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['month_cycle', 'company_id']);
        });

        Schema::create('family_child_details', function (Blueprint $table) {
            $table->id();
            $table->string('month_cycle');
            $table->string('company_id');
            $table->string('child_name');
            $table->integer('age')->nullable();
            $table->boolean('school_going')->default(false);
            $table->string('school_name')->nullable();
            $table->string('class_name')->nullable();
            $table->boolean('van_using_child')->default(false);
            $table->integer('sort_order')->default(1);
            $table->timestamps();

            $table->index(['month_cycle', 'company_id']);
        });

        Schema::create('employees_registry', function (Blueprint $table) {
            $table->string('company_id')->primary();
            $table->string('name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('cnic_no')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->string('colony_type')->nullable();
            $table->string('block_floor')->nullable();
            $table->string('room_no')->nullable();
            $table->string('active')->default('Yes');
            $table->string('remarks')->nullable();
            $table->string('unit_id')->nullable();
            $table->timestamps();
        });

        Schema::create('billing_rows', function (Blueprint $table) {
            $table->id();
            $table->string('month_cycle');
            $table->string('company_id');
            $table->string('unit_id')->nullable();
            $table->decimal('water_amt', 12, 2)->default(0);
            $table->decimal('power_amt', 12, 2)->default(0);
            $table->decimal('drink_amt', 12, 2)->default(0);
            $table->decimal('total_amt', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['month_cycle', 'company_id']);
            $table->index(['month_cycle', 'unit_id']);
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('month_cycle');
            $table->string('severity')->nullable();
            $table->string('code')->nullable();
            $table->text('message')->nullable();
            $table->text('ref_json')->nullable();
            $table->timestamps();

            $table->index(['month_cycle', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
        Schema::dropIfExists('billing_rows');
        Schema::dropIfExists('employees_registry');
        Schema::dropIfExists('family_child_details');
        Schema::dropIfExists('family_details');
    }
};
