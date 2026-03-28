<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('util_month_cycle')) {
            Schema::create('util_month_cycle', function (Blueprint $table) {
                $table->string('month_cycle')->primary();
                $table->string('state')->default('OPEN');
                $table->string('locked_by_user_id')->nullable();
                $table->timestamp('locked_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('util_rate_monthly')) {
            Schema::create('util_rate_monthly', function (Blueprint $table) {
                $table->string('month_cycle')->primary();
                $table->decimal('elec_rate', 14, 4)->default(0);
                $table->decimal('water_general_rate', 14, 4)->default(0);
                $table->decimal('water_drinking_rate', 14, 4)->default(0);
                $table->decimal('school_van_rate', 14, 4)->default(0);
                $table->string('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('util_monthly_rates_config')) {
            Schema::create('util_monthly_rates_config', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle')->unique();
                $table->decimal('elec_rate', 14, 4)->default(0);
                $table->decimal('water_general_rate', 14, 4)->default(0);
                $table->decimal('water_drinking_rate', 14, 4)->default(0);
                $table->decimal('school_van_rate', 14, 4)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('monthly_variable_expenses')) {
            Schema::create('monthly_variable_expenses', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle');
                $table->string('expense_type');
                $table->decimal('amount', 14, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['month_cycle', 'expense_type']);
            });
        }

        if (!Schema::hasTable('util_billing_run')) {
            Schema::create('util_billing_run', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle');
                $table->string('run_key');
                $table->string('run_status')->default('DRAFT');
                $table->string('started_by_user_id')->nullable();
                $table->timestamps();
                $table->unique(['month_cycle', 'run_key']);
            });
        }

        if (!Schema::hasTable('util_billing_line')) {
            Schema::create('util_billing_line', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('billing_run_id');
                $table->string('month_cycle');
                $table->string('employee_id');
                $table->string('utility_type');
                $table->decimal('qty', 14, 4)->default(0);
                $table->decimal('rate', 14, 4)->default(0);
                $table->decimal('amount', 14, 2)->default(0);
                $table->string('source_ref')->nullable();
                $table->timestamps();
                $table->unique(['billing_run_id', 'employee_id', 'utility_type'], 'uq_util_billing_line_run_emp_utility');
                $table->index(['month_cycle', 'employee_id']);
            });
        }

        if (!Schema::hasTable('util_audit_log')) {
            Schema::create('util_audit_log', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type');
                $table->string('entity_id');
                $table->string('action');
                $table->string('actor_user_id');
                $table->text('before_json')->nullable();
                $table->text('after_json')->nullable();
                $table->string('correlation_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['entity_type', 'entity_id', 'created_at'], 'idx_util_audit_entity');
            });
        }

        if (!Schema::hasTable('util_recovery_payment')) {
            Schema::create('util_recovery_payment', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle');
                $table->string('employee_id');
                $table->decimal('amount_paid', 14, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['month_cycle', 'employee_id']);
            });
        }

        if (!Schema::hasTable('util_formula_result')) {
            Schema::create('util_formula_result', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle');
                $table->string('employee_id');
                $table->decimal('elec_units', 14, 4)->default(0);
                $table->decimal('elec_amount', 14, 2)->default(0);
                $table->decimal('chargeable_general_water_liters', 14, 4)->default(0);
                $table->decimal('water_general_amount', 14, 2)->default(0);
                $table->timestamps();
                $table->index(['month_cycle', 'employee_id']);
            });
        }

        if (!Schema::hasTable('util_drinking_formula_result')) {
            Schema::create('util_drinking_formula_result', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle');
                $table->string('employee_id');
                $table->decimal('billed_liters', 14, 4)->default(0);
                $table->decimal('rate', 14, 4)->default(0);
                $table->decimal('amount', 14, 2)->default(0);
                $table->timestamps();
                $table->index(['month_cycle', 'employee_id']);
            });
        }

        if (!Schema::hasTable('util_school_van_monthly_charge')) {
            Schema::create('util_school_van_monthly_charge', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle');
                $table->string('employee_id');
                $table->string('child_name')->nullable();
                $table->string('school_name')->nullable();
                $table->string('class_level')->nullable();
                $table->string('service_mode')->nullable();
                $table->decimal('rate', 14, 2)->default(0);
                $table->decimal('amount', 14, 2)->default(0);
                $table->boolean('charged_flag')->default(true);
                $table->timestamps();
                $table->index(['month_cycle', 'employee_id']);
            });
        }

        if (!Schema::hasTable('util_elec_employee_share_monthly')) {
            Schema::create('util_elec_employee_share_monthly', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle');
                $table->string('unit_id');
                $table->string('employee_id');
                $table->decimal('attendance', 14, 4)->default(0);
                $table->decimal('share_units', 14, 4)->default(0);
                $table->decimal('share_amount', 14, 2)->default(0);
                $table->string('allocation_method')->nullable();
                $table->decimal('explain_usage_share_units', 14, 4)->default(0);
                $table->decimal('explain_free_share_units', 14, 4)->default(0);
                $table->decimal('explain_billable_units', 14, 4)->default(0);
                $table->timestamps();
                $table->index(['month_cycle', 'employee_id']);
            });
        }

        if (!Schema::hasTable('util_elec_unit_monthly_result')) {
            Schema::create('util_elec_unit_monthly_result', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle');
                $table->string('unit_id');
                $table->string('category')->nullable();
                $table->decimal('usage_units', 14, 4)->default(0);
                $table->integer('rooms_count')->default(0);
                $table->decimal('unit_free_units', 14, 4)->default(0);
                $table->decimal('net_units', 14, 4)->default(0);
                $table->decimal('elec_rate', 14, 4)->default(0);
                $table->decimal('unit_amount', 14, 2)->default(0);
                $table->decimal('total_attendance', 14, 4)->default(0);
                $table->timestamps();
                $table->unique(['month_cycle', 'unit_id']);
            });
        }

        if (!Schema::hasTable('util_water_employee_share_monthly')) {
            Schema::create('util_water_employee_share_monthly', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle');
                $table->string('unit_id');
                $table->string('employee_id');
                $table->string('water_zone')->nullable();
                $table->decimal('attendance', 14, 4)->default(0);
                $table->decimal('basis_persons', 14, 4)->default(0);
                $table->decimal('basis_attendance', 14, 4)->default(0);
                $table->decimal('share_liters', 14, 4)->default(0);
                $table->decimal('share_amount', 14, 2)->default(0);
                $table->string('allocation_method')->nullable();
                $table->decimal('explain_usage_share_liters', 14, 4)->default(0);
                $table->decimal('explain_free_share_liters', 14, 4)->default(0);
                $table->decimal('explain_billable_liters', 14, 4)->default(0);
                $table->timestamps();
                $table->index(['month_cycle', 'employee_id']);
            });
        }

        if (!Schema::hasTable('billing_run')) {
            Schema::create('billing_run', function (Blueprint $table) {
                $table->string('run_id')->primary();
                $table->string('month_cycle')->index();
                $table->string('status')->default('draft');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finalized_at')->nullable();
                $table->string('fingerprint')->nullable();
            });
        }

        if (!Schema::hasTable('billing_rows')) {
            Schema::create('billing_rows', function (Blueprint $table) {
                $table->id();
                $table->string('run_id');
                $table->string('month_cycle');
                $table->string('company_id');
                $table->string('unit_id')->nullable();
                $table->decimal('water_amt', 14, 2)->default(0);
                $table->decimal('power_amt', 14, 2)->default(0);
                $table->decimal('drink_amt', 14, 2)->default(0);
                $table->decimal('adjustment', 14, 2)->default(0);
                $table->decimal('total_amt', 14, 2)->default(0);
                $table->decimal('rounded_2dp', 14, 2)->default(0);
                $table->timestamps();
                $table->index(['month_cycle', 'company_id']);
            });
        }

        if (!Schema::hasTable('hr_input')) {
            Schema::create('hr_input', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle');
                $table->string('company_id');
                $table->decimal('active_days', 14, 4)->default(0);
                $table->unique(['month_cycle', 'company_id']);
            });
        }

        if (!Schema::hasTable('map_room')) {
            Schema::create('map_room', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle');
                $table->string('unit_id');
                $table->string('company_id');
                $table->index(['month_cycle', 'company_id']);
            });
        }

        if (!Schema::hasTable('readings')) {
            Schema::create('readings', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle');
                $table->string('meter_id')->nullable();
                $table->string('unit_id');
                $table->string('meter_type')->nullable();
                $table->decimal('usage', 14, 4)->default(0);
                $table->decimal('amount', 14, 2)->default(0);
                $table->index(['month_cycle', 'unit_id']);
            });
        }

        if (!Schema::hasTable('ro_drinking')) {
            Schema::create('ro_drinking', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle');
                $table->string('unit_id');
                $table->decimal('liters', 14, 4)->default(0);
                $table->decimal('amount', 14, 2)->default(0);
                $table->index(['month_cycle', 'unit_id']);
            });
        }

        if (!Schema::hasTable('logs')) {
            Schema::create('logs', function (Blueprint $table) {
                $table->id();
                $table->string('run_id')->nullable();
                $table->string('month_cycle');
                $table->string('severity');
                $table->string('code');
                $table->text('message');
                $table->text('ref_json')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index('month_cycle');
            });
        }

        if (!Schema::hasTable('finalized_months')) {
            Schema::create('finalized_months', function (Blueprint $table) {
                $table->string('month_cycle')->primary();
                $table->timestamp('finalized_at')->useCurrent();
            });
        }

        DB::statement('CREATE VIEW IF NOT EXISTS "Employees_Master" AS SELECT company_id AS "CompanyID", name AS "Name", department AS "Department", unit_id AS "Unit_ID", active AS "Active" FROM employees_master');
    }

    public function down(): void
    {
        Schema::dropIfExists('finalized_months');
        DB::statement('DROP VIEW IF EXISTS "Employees_Master"');
        Schema::dropIfExists('logs');
        Schema::dropIfExists('ro_drinking');
        Schema::dropIfExists('readings');
        Schema::dropIfExists('map_room');
        Schema::dropIfExists('hr_input');
        Schema::dropIfExists('billing_rows');
        Schema::dropIfExists('billing_run');
        Schema::dropIfExists('util_water_employee_share_monthly');
        Schema::dropIfExists('util_elec_unit_monthly_result');
        Schema::dropIfExists('util_elec_employee_share_monthly');
        Schema::dropIfExists('util_school_van_monthly_charge');
        Schema::dropIfExists('util_drinking_formula_result');
        Schema::dropIfExists('util_formula_result');
        Schema::dropIfExists('util_recovery_payment');
        Schema::dropIfExists('util_audit_log');
        Schema::dropIfExists('util_billing_line');
        Schema::dropIfExists('util_billing_run');
        Schema::dropIfExists('monthly_variable_expenses');
        Schema::dropIfExists('util_monthly_rates_config');
        Schema::dropIfExists('util_rate_monthly');
        Schema::dropIfExists('util_month_cycle');
    }
};