<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('util_billing_run', function (Blueprint $table) {
            if (!Schema::hasColumn('util_billing_run', 'cycle_start_date')) {
                $table->date('cycle_start_date')->nullable()->after('month_cycle');
            }

            if (!Schema::hasColumn('util_billing_run', 'cycle_end_date')) {
                $table->date('cycle_end_date')->nullable()->after('cycle_start_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('util_billing_run', function (Blueprint $table) {
            if (Schema::hasColumn('util_billing_run', 'cycle_end_date')) {
                $table->dropColumn('cycle_end_date');
            }

            if (Schema::hasColumn('util_billing_run', 'cycle_start_date')) {
                $table->dropColumn('cycle_start_date');
            }
        });
    }
};
