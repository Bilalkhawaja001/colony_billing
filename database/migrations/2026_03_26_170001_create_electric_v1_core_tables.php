<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('electric_v1_allowance', function (Blueprint $t) {
            $t->id();
            $t->string('unit_id');
            $t->decimal('free_electric', 14, 4)->default(0);
            $t->string('unit_name')->nullable();
            $t->string('residence_type', 16);
            $t->timestamp('updated_at')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->index(['unit_id', 'updated_at'], 'ev1_allow_unit_updated_idx');
        });

        Schema::create('electric_v1_readings', function (Blueprint $t) {
            $t->id();
            $t->date('cycle_start_date');
            $t->date('cycle_end_date');
            $t->string('unit_id');
            $t->decimal('previous_reading', 14, 4);
            $t->decimal('current_reading', 14, 4);
            $t->string('reading_status', 16);
            $t->timestamp('updated_at')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->index(['unit_id', 'cycle_start_date', 'cycle_end_date', 'updated_at'], 'ev1_read_cycle_unit_updated_idx');
        });

        Schema::create('electric_v1_hr_attendance', function (Blueprint $t) {
            $t->id();
            $t->date('cycle_start_date');
            $t->date('cycle_end_date');
            $t->string('company_id');
            $t->decimal('attendance_days', 14, 4)->default(0);
            $t->timestamp('updated_at')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->unique(['company_id', 'cycle_start_date', 'cycle_end_date']);
        });

        Schema::create('electric_v1_occupancy', function (Blueprint $t) {
            $t->id();
            $t->string('company_id');
            $t->string('unit_id');
            $t->string('room_id');
            $t->date('from_date');
            $t->date('to_date');
            $t->timestamp('updated_at')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->unique(['company_id', 'unit_id', 'room_id', 'from_date', 'to_date'], 'ev1_occ_uq');
        });

        Schema::create('electric_v1_adjustments', function (Blueprint $t) {
            $t->id();
            $t->date('cycle_start_date');
            $t->date('cycle_end_date');
            $t->string('company_id');
            $t->string('unit_id');
            $t->decimal('adjustment_units', 14, 4)->default(0);
            $t->timestamp('updated_at')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->unique(['company_id', 'unit_id', 'cycle_start_date', 'cycle_end_date'], 'ev1_adj_uq');
        });

        Schema::create('electric_v1_output_employee_final', function (Blueprint $t) {
            $t->id();
            $t->date('cycle_start_date');
            $t->date('cycle_end_date');
            $t->string('run_id');
            $t->string('company_id');
            $t->string('name')->nullable();
            $t->decimal('total_net_billable_units', 14, 4)->default(0);
            $t->decimal('flat_rate', 14, 4)->default(0);
            $t->decimal('final_amount_before_rounding', 14, 4)->default(0);
            $t->decimal('final_amount_rounded', 14, 4)->default(0);
            $t->string('has_estimated_units', 1)->default('N');
            $t->index(['cycle_start_date', 'cycle_end_date', 'run_id'], 'ev1_out_final_idx');
        });

        Schema::create('electric_v1_output_employee_unit_drilldown', function (Blueprint $t) {
            $t->id();
            $t->date('cycle_start_date');
            $t->date('cycle_end_date');
            $t->string('run_id');
            $t->string('company_id');
            $t->string('unit_id');
            $t->string('residence_type', 16);
            $t->decimal('employee_attendance_in_unit', 14, 4)->default(0);
            $t->decimal('gross_units', 14, 4)->default(0);
            $t->decimal('free_allowance_units', 14, 4)->default(0);
            $t->decimal('net_units_before_adj', 14, 4)->default(0);
            $t->decimal('adjustment_units', 14, 4)->default(0);
            $t->decimal('net_units_after_adj', 14, 4)->default(0);
            $t->decimal('amount_before_rounding', 14, 4)->default(0);
            $t->string('is_estimated', 1)->default('N');
            $t->string('estimate_source_cycle1')->nullable();
            $t->string('estimate_source_cycle2')->nullable();
            $t->string('estimate_source_cycle3')->nullable();
            $t->unsignedInteger('estimated_from_valid_cycle_count')->default(0);
            $t->index(['cycle_start_date', 'cycle_end_date', 'run_id'], 'ev1_out_drill_idx');
        });

        Schema::create('electric_v1_exception_log', function (Blueprint $t) {
            $t->id();
            $t->string('run_id');
            $t->timestamp('logged_at');
            $t->string('severity', 16);
            $t->string('exception_code', 64);
            $t->text('message');
            $t->string('company_id')->nullable();
            $t->string('unit_id')->nullable();
            $t->string('room_id')->nullable();
            $t->date('cycle_start_date');
            $t->date('cycle_end_date');
            $t->index(['cycle_start_date', 'cycle_end_date', 'run_id'], 'ev1_exc_idx');
        });

        Schema::create('electric_v1_run_history', function (Blueprint $t) {
            $t->id();
            $t->string('run_id')->unique();
            $t->timestamp('run_start');
            $t->timestamp('run_end')->nullable();
            $t->date('cycle_start_date');
            $t->date('cycle_end_date');
            $t->string('status', 64);
            $t->unsignedInteger('processed_count')->default(0);
            $t->unsignedInteger('skipped_count')->default(0);
            $t->unsignedInteger('exception_count')->default(0);
            $t->index(['cycle_start_date', 'cycle_end_date', 'run_start'], 'ev1_run_hist_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electric_v1_run_history');
        Schema::dropIfExists('electric_v1_exception_log');
        Schema::dropIfExists('electric_v1_output_employee_unit_drilldown');
        Schema::dropIfExists('electric_v1_output_employee_final');
        Schema::dropIfExists('electric_v1_adjustments');
        Schema::dropIfExists('electric_v1_occupancy');
        Schema::dropIfExists('electric_v1_hr_attendance');
        Schema::dropIfExists('electric_v1_readings');
        Schema::dropIfExists('electric_v1_allowance');
    }
};
