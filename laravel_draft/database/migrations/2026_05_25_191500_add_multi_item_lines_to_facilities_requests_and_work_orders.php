<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('facility_service_request_items')) {
            Schema::create('facility_service_request_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('facility_service_request_id');
                $table->unsignedInteger('line_no');
                $table->unsignedBigInteger('facility_component_type_id');
                $table->string('work_action', 40);
                $table->text('problem_detail');
                $table->string('part_material_used', 255)->nullable();
                $table->decimal('quantity', 12, 2)->default(1);
                $table->string('unit', 40)->nullable();
                $table->decimal('unit_cost', 12, 2)->default(0);
                $table->decimal('total_cost', 12, 2)->default(0);
                $table->string('material_source', 40)->nullable();
                $table->text('remarks')->nullable();
                $table->string('status', 40)->default('OPEN');
                $table->timestamps();

                $table->unique(['facility_service_request_id', 'line_no'], 'fsri_request_line_unique');
                $table->index('facility_component_type_id', 'fsri_component_idx');
                $table->index('status', 'fsri_status_idx');

                $table->foreign('facility_service_request_id', 'fsri_request_fk')
                    ->references('id')
                    ->on('facility_service_requests')
                    ->cascadeOnDelete();

                $table->foreign('facility_component_type_id', 'fsri_component_fk')
                    ->references('id')
                    ->on('facility_component_types')
                    ->restrictOnDelete();
            });
        }

        if (!Schema::hasTable('facility_work_order_items')) {
            Schema::create('facility_work_order_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('facility_work_order_id');
                $table->unsignedBigInteger('source_request_item_id')->nullable();
                $table->unsignedInteger('line_no');
                $table->unsignedBigInteger('facility_component_type_id');
                $table->string('work_action', 40);
                $table->text('problem_detail');
                $table->string('part_material_used', 255)->nullable();
                $table->decimal('quantity', 12, 2)->default(1);
                $table->string('unit', 40)->nullable();
                $table->decimal('estimated_unit_cost', 12, 2)->default(0);
                $table->decimal('estimated_total_cost', 12, 2)->default(0);
                $table->decimal('actual_unit_cost', 12, 2)->nullable();
                $table->decimal('actual_total_cost', 12, 2)->nullable();
                $table->string('material_source', 40)->nullable();
                $table->text('remarks')->nullable();
                $table->string('status', 40)->default('OPEN');
                $table->timestamps();

                $table->unique(['facility_work_order_id', 'line_no'], 'fwoi_order_line_unique');
                $table->index('source_request_item_id', 'fwoi_source_item_idx');
                $table->index('facility_component_type_id', 'fwoi_component_idx');
                $table->index('status', 'fwoi_status_idx');

                $table->foreign('facility_work_order_id', 'fwoi_order_fk')
                    ->references('id')
                    ->on('facility_work_orders')
                    ->cascadeOnDelete();

                $table->foreign('source_request_item_id', 'fwoi_source_item_fk')
                    ->references('id')
                    ->on('facility_service_request_items')
                    ->nullOnDelete();

                $table->foreign('facility_component_type_id', 'fwoi_component_fk')
                    ->references('id')
                    ->on('facility_component_types')
                    ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_work_order_items');
        Schema::dropIfExists('facility_service_request_items');
    }
};
