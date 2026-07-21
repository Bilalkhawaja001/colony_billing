<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('electric_v1_allowance', 'is_active')) {
            Schema::table('electric_v1_allowance', function (Blueprint $table) {
                $table->boolean('is_active')
                    ->default(true)
                    ->after('residence_type')
                    ->index('ev1_allow_is_active_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('electric_v1_allowance', 'is_active')) {
            Schema::table('electric_v1_allowance', function (Blueprint $table) {
                $table->dropIndex('ev1_allow_is_active_idx');
                $table->dropColumn('is_active');
            });
        }
    }
};
