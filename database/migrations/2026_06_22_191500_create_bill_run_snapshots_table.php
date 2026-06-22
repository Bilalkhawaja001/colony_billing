<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_run_snapshots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('bill_run_id')->constrained('bill_runs')->restrictOnDelete();
            $table->string('snapshot_type', 40);
            $table->string('month_cycle', 7);
            $table->date('cycle_start_date');
            $table->date('cycle_end_date');
            $table->string('source_table', 80);
            $table->string('source_filter_hash', 64);
            $table->unsignedInteger('row_count')->default(0);
            $table->char('snapshot_hash', 64);
            $table->longText('summary_json')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['bill_run_id', 'snapshot_type'], 'bill_run_snapshots_run_type_unique');
            $table->index(['month_cycle', 'snapshot_type'], 'bill_run_snapshots_period_type_idx');
            $table->index(['source_table', 'source_filter_hash'], 'bill_run_snapshots_source_idx');
        });

        Schema::create('bill_run_snapshot_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('snapshot_id')->constrained('bill_run_snapshots')->cascadeOnDelete();
            $table->string('entity_type', 50);
            $table->string('entity_id', 120);
            $table->unsignedInteger('sort_order')->default(0);
            $table->char('row_hash', 64);
            $table->longText('payload_json');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['snapshot_id', 'entity_type', 'entity_id'], 'bill_snapshot_items_entity_unique');
            $table->index(['entity_type', 'entity_id'], 'bill_snapshot_items_entity_idx');
            $table->index(['snapshot_id', 'sort_order'], 'bill_snapshot_items_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_run_snapshot_items');
        Schema::dropIfExists('bill_run_snapshots');
    }
};
