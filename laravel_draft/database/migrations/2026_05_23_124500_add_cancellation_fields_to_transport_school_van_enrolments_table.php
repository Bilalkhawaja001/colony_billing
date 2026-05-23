<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transport_school_van_enrolments')) {
            return;
        }

        Schema::table('transport_school_van_enrolments', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_school_van_enrolments', 'cancel_reason')) {
                $table->string('cancel_reason', 60)->nullable()->after('remarks');
            }

            if (!Schema::hasColumn('transport_school_van_enrolments', 'cancellation_remarks')) {
                $table->text('cancellation_remarks')->nullable()->after('cancel_reason');
            }

            if (!Schema::hasColumn('transport_school_van_enrolments', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancellation_remarks');
            }

            if (!Schema::hasColumn('transport_school_van_enrolments', 'cancelled_by_user_id')) {
                $table->string('cancelled_by_user_id', 50)->nullable()->after('cancelled_at');
            }

            if (!Schema::hasColumn('transport_school_van_enrolments', 'reactivation_reason')) {
                $table->string('reactivation_reason', 60)->nullable()->after('cancelled_by_user_id');
            }

            if (!Schema::hasColumn('transport_school_van_enrolments', 'reactivation_remarks')) {
                $table->text('reactivation_remarks')->nullable()->after('reactivation_reason');
            }

            if (!Schema::hasColumn('transport_school_van_enrolments', 'reactivated_at')) {
                $table->timestamp('reactivated_at')->nullable()->after('reactivation_remarks');
            }

            if (!Schema::hasColumn('transport_school_van_enrolments', 'reactivated_by_user_id')) {
                $table->string('reactivated_by_user_id', 50)->nullable()->after('reactivated_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('transport_school_van_enrolments')) {
            return;
        }

        Schema::table('transport_school_van_enrolments', function (Blueprint $table) {
            foreach ([
                'reactivated_by_user_id',
                'reactivated_at',
                'reactivation_remarks',
                'reactivation_reason',
                'cancelled_by_user_id',
                'cancelled_at',
                'cancellation_remarks',
                'cancel_reason',
            ] as $column) {
                if (Schema::hasColumn('transport_school_van_enrolments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
