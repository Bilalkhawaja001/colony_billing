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
            if (!Schema::hasColumn('transport_school_van_enrolments', 'left_reason')) {
                $table->string('left_reason', 60)->nullable()->after('left_on');
            }

            if (!Schema::hasColumn('transport_school_van_enrolments', 'left_remarks')) {
                $table->text('left_remarks')->nullable()->after('left_reason');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('transport_school_van_enrolments')) {
            return;
        }

        Schema::table('transport_school_van_enrolments', function (Blueprint $table) {
            foreach (['left_remarks', 'left_reason'] as $column) {
                if (Schema::hasColumn('transport_school_van_enrolments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
