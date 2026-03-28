<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('util_unit', function (Blueprint $table) {
            $table->string('unit_id')->primary();
            $table->string('colony_type')->nullable();
            $table->string('block_name')->nullable();
            $table->string('room_no')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('util_unit');
    }
};
