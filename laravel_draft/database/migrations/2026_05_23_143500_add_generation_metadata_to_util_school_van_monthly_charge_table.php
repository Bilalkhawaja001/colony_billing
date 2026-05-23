<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('util_school_van_monthly_charge')) {
            return;
        }

        Schema::table('util_school_van_monthly_charge', function (Blueprint $table) {
            if (!Schema::hasColumn('util_school_van_monthly_charge', 'enrolment_id')) {
                $table->unsignedBigInteger('enrolment_id')->nullable()->after('employee_id');
            }

            if (!Schema::hasColumn('util_school_van_monthly_charge', 'family_member_id')) {
                $table->unsignedBigInteger('family_member_id')->nullable()->after('enrolment_id');
            }

            if (!Schema::hasColumn('util_school_van_monthly_charge', 'charge_factor')) {
                $table->decimal('charge_factor', 8, 4)->default(1)->after('service_mode');
            }

            if (!Schema::hasColumn('util_school_van_monthly_charge', 'charge_rule')) {
                $table->string('charge_rule', 80)->nullable()->after('charge_factor');
            }

            if (!Schema::hasColumn('util_school_van_monthly_charge', 'rounding_adjustment')) {
                $table->decimal('rounding_adjustment', 14, 2)->default(0)->after('amount');
            }

            if (!Schema::hasColumn('util_school_van_monthly_charge', 'generated_at')) {
                $table->timestamp('generated_at')->nullable()->after('charged_flag');
            }

            if (!Schema::hasColumn('util_school_van_monthly_charge', 'generated_by_user_id')) {
                $table->string('generated_by_user_id', 50)->nullable()->after('generated_at');
            }
        });

        Schema::table('util_school_van_monthly_charge', function (Blueprint $table) {
            $table->unique(['month_cycle', 'enrolment_id'], 'sv_month_enrolment_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('util_school_van_monthly_charge')) {
            return;
        }

        Schema::table('util_school_van_monthly_charge', function (Blueprint $table) {
            $table->dropUnique('sv_month_enrolment_unique');

            foreach ([
                'generated_by_user_id',
                'generated_at',
                'rounding_adjustment',
                'charge_rule',
                'charge_factor',
                'family_member_id',
                'enrolment_id',
            ] as $column) {
                if (Schema::hasColumn('util_school_van_monthly_charge', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
