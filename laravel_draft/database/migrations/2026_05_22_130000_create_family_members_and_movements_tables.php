<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();

            // Permanent link: family belongs to employee, not house.
            $table->string('company_id');
            $table->string('member_name');
            $table->string('relation');
            $table->integer('age')->nullable();

            // School information from initial family master source.
            $table->boolean('school_going')->default(false);
            $table->string('school_name')->nullable();
            $table->string('class_name')->nullable();

            // Initial master import/reference fields only.
            $table->string('source_month_cycle', 7)->nullable();
            $table->string('source_colony_type')->nullable();
            $table->string('source_block_floor')->nullable();
            $table->string('source_room_no')->nullable();

            // Current master status; movements remain in history table.
            $table->string('current_status', 30)->default('PRESENT');
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'relation']);
            $table->index(['current_status']);
        });

        Schema::create('family_member_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_member_id')
                ->constrained('family_members')
                ->cascadeOnDelete();

            $table->string('movement_type', 30); // MOVE_OUT / RETURN_BACK
            $table->date('movement_date');
            $table->text('remarks')->nullable();
            $table->string('created_by')->nullable();

            $table->timestamps();

            $table->index(['family_member_id', 'movement_date']);
            $table->index(['movement_type', 'movement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_member_movements');
        Schema::dropIfExists('family_members');
    }
};
