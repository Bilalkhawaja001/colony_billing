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
            if (!Schema::hasColumn('transport_school_van_enrolments', 'cancelled_from_status')) {
                $table->string('cancelled_from_status', 20)->nullable()->after('cancel_reason');
            }

            if (!Schema::hasColumn('transport_school_van_enrolments', 'cancellation_reversal_reason')) {
                $table->string('cancellation_reversal_reason', 60)->nullable()->after('reactivated_by_user_id');
            }

            if (!Schema::hasColumn('transport_school_van_enrolments', 'cancellation_reversal_remarks')) {
                $table->text('cancellation_reversal_remarks')->nullable()->after('cancellation_reversal_reason');
            }

            if (!Schema::hasColumn('transport_school_van_enrolments', 'cancellation_reversed_at')) {
                $table->timestamp('cancellation_reversed_at')->nullable()->after('cancellation_reversal_remarks');
            }

            if (!Schema::hasColumn('transport_school_van_enrolments', 'cancellation_reversed_by_user_id')) {
                $table->string('cancellation_reversed_by_user_id', 50)->nullable()->after('cancellation_reversed_at');
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
                'cancellation_reversed_by_user_id',
                'cancellation_reversed_at',
                'cancellation_reversal_remarks',
                'cancellation_reversal_reason',
                'cancelled_from_status',
            ] as $column) {
                if (Schema::hasColumn('transport_school_van_enrolments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
