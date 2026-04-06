<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees_master', function (Blueprint $table) {
            $table->string('company_id')->primary();
            $table->string('name');
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->string('unit_id')->nullable();
            $table->string('colony_type')->nullable();
            $table->string('block_floor')->nullable();
            $table->string('room_no')->nullable();
            $table->string('active')->default('Yes');
            $table->timestamps();

            $table->index(['active', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees_master');
    }
};
