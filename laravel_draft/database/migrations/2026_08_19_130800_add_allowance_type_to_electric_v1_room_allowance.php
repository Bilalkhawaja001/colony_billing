<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('electric_v1_room_allowance', function (Blueprint $table) {
            $table->string('allowance_type', 32)
                ->nullable()
                ->after('room_free_allowance');
        });

        // Safe backfill only where the legacy unit has one clear allowance type.
        DB::statement("
            UPDATE electric_v1_room_allowance r
            JOIN (
                SELECT
                    BINARY UPPER(TRIM(unit_id)) AS unit_key,
                    MAX(UPPER(TRIM(allowance_type))) AS allowance_type
                FROM electric_v1_allowance
                WHERE UPPER(TRIM(allowance_type))
                    IN ('BACHELOR','SENIOR_STAFF','FAMILY','COMMON')
                GROUP BY BINARY UPPER(TRIM(unit_id))
                HAVING COUNT(DISTINCT UPPER(TRIM(allowance_type))) = 1
            ) a
              ON BINARY UPPER(TRIM(r.unit_id)) = a.unit_key
            SET r.allowance_type = a.allowance_type
            WHERE r.allowance_type IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('electric_v1_room_allowance', function (Blueprint $table) {
            $table->dropColumn('allowance_type');
        });
    }
};
