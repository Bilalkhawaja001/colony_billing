<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transport_school_van_enrolments')) {
            return;
        }

        Schema::create('transport_school_van_enrolments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_member_id')
                ->constrained('family_members')
                ->restrictOnDelete();

            $table->foreignId('vehicle_id')
                ->nullable()
                ->constrained('transport_vehicles')
                ->nullOnDelete();

            $table->date('joined_on');
            $table->date('left_on')->nullable();

            $table->string('status', 20)->default('ACTIVE');
            $table->string('source', 30)->default('FAMILY_DETAILS');

            $table->string('route_label')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['family_member_id', 'status'], 'tsve_member_status_idx');
            $table->index(['status', 'joined_on', 'left_on'], 'tsve_status_dates_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_school_van_enrolments');
    }
};
