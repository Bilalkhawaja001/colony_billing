<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('util_month_cycle')) {
            return;
        }

        Schema::table('util_month_cycle', function (Blueprint $table) {
            if (!Schema::hasColumn('util_month_cycle', 'cycle_start_date')) {
                $table->date('cycle_start_date')->nullable()->after('state');
            }

            if (!Schema::hasColumn('util_month_cycle', 'cycle_end_date')) {
                $table->date('cycle_end_date')->nullable()->after('cycle_start_date');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('util_month_cycle')) {
            return;
        }

        Schema::table('util_month_cycle', function (Blueprint $table) {
            foreach (['cycle_end_date', 'cycle_start_date'] as $column) {
                if (Schema::hasColumn('util_month_cycle', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
