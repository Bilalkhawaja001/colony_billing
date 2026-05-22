<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE family_members CHANGE source_colony_type source_residence_type VARCHAR(255) NULL');
        DB::statement('ALTER TABLE family_members ADD source_colony_building_name VARCHAR(255) NULL AFTER source_residence_type');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE family_members DROP COLUMN source_colony_building_name');
        DB::statement('ALTER TABLE family_members CHANGE source_residence_type source_colony_type VARCHAR(255) NULL');
    }
};
