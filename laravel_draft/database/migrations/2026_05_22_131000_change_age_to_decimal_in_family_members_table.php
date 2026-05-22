<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE family_members MODIFY age DECIMAL(5,1) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE family_members MODIFY age INT NULL');
    }
};
