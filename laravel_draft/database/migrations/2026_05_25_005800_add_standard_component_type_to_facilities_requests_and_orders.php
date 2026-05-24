<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('facility_service_requests', 'facility_component_type_id')) {
            Schema::table('facility_service_requests', function (Blueprint $table): void {
                $table->unsignedBigInteger('facility_component_type_id')
                    ->nullable()
                    ->after('facility_component_id');

                $table->index('facility_component_type_id', 'fsr_component_type_idx');
                $table->foreign('facility_component_type_id', 'fsr_component_type_fk')
                    ->references('id')
                    ->on('facility_component_types')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('facility_work_orders', 'facility_component_type_id')) {
            Schema::table('facility_work_orders', function (Blueprint $table): void {
                $table->unsignedBigInteger('facility_component_type_id')
                    ->nullable()
                    ->after('facility_component_id');

                $table->index('facility_component_type_id', 'fwo_component_type_idx');
                $table->foreign('facility_component_type_id', 'fwo_component_type_fk')
                    ->references('id')
                    ->on('facility_component_types')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('facility_work_orders', 'facility_component_type_id')) {
            Schema::table('facility_work_orders', function (Blueprint $table): void {
                $table->dropForeign('fwo_component_type_fk');
                $table->dropIndex('fwo_component_type_idx');
                $table->dropColumn('facility_component_type_id');
            });
        }

        if (Schema::hasColumn('facility_service_requests', 'facility_component_type_id')) {
            Schema::table('facility_service_requests', function (Blueprint $table): void {
                $table->dropForeign('fsr_component_type_fk');
                $table->dropIndex('fsr_component_type_idx');
                $table->dropColumn('facility_component_type_id');
            });
        }
    }
};
