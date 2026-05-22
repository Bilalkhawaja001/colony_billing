<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('util_unit_room_snapshot', function (Blueprint $table) {
            if (!Schema::hasColumn('util_unit_room_snapshot', 'residence_type')) {
                $table->string('residence_type', 40)->nullable()->after('unit_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('util_unit_room_snapshot', function (Blueprint $table) {
            if (Schema::hasColumn('util_unit_room_snapshot', 'residence_type')) {
                $table->dropColumn('residence_type');
            }
        });
    }
};
