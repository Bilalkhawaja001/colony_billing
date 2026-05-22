<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_residence_assignments', function (Blueprint $table) {
            $table->id();

            // Employee identity
            $table->string('company_id', 255)->index();

            // Physical residence assigned to the employee.
            // Values come from room registry and remain flexible for future categories.
            $table->string('residence_type', 100);
            $table->string('category', 255)->nullable();
            $table->string('unit_id', 255);
            $table->string('block_floor', 255)->nullable();
            $table->string('room_no', 255);

            // INDIVIDUAL = employee alone / normal personal accommodation.
            // HOUSEHOLD  = employee occupying a family house with linked family.
            $table->string('occupancy_mode', 30)->default('INDIVIDUAL');

            // Date-wise assignment history.
            $table->date('start_date');
            $table->date('end_date')->nullable();

            // ACTIVE assignment has no end date.
            // CLOSED assignment retains full historic record.
            $table->string('status', 30)->default('ACTIVE');

            // INITIAL_IMPORT, ASSIGN, SHIFT, VACATE, FAMILY_SENT_BACK
            $table->string('start_reason', 50)->nullable();
            $table->string('closure_reason', 50)->nullable();

            // Provenance of initial/imported/current records.
            $table->string('source_month_cycle', 7)->nullable();
            $table->string('source_record_type', 50)->nullable();

            $table->text('remarks')->nullable();
            $table->string('created_by', 255)->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status'], 'era_company_status_idx');
            $table->index(['unit_id', 'room_no', 'status'], 'era_room_status_idx');
            $table->index(['residence_type', 'status'], 'era_type_status_idx');
            $table->index(['occupancy_mode', 'status'], 'era_mode_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_residence_assignments');
    }
};
