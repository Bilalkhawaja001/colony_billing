<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_run_preflight_checks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('bill_run_id')->constrained('bill_runs')->cascadeOnDelete();
            $table->string('check_code', 80);
            $table->string('severity', 20)->default('info');
            $table->string('status', 20)->default('pending');
            $table->string('title', 180);
            $table->string('message', 500)->nullable();
            $table->string('entity_type', 50)->nullable();
            $table->string('entity_id', 120)->nullable();
            $table->string('source_table', 80)->nullable();
            $table->longText('meta_json')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['bill_run_id', 'check_code', 'entity_type', 'entity_id'], 'bill_run_preflight_unique');
            $table->index(['bill_run_id', 'status', 'severity'], 'bill_run_preflight_run_status_idx');
            $table->index(['check_code', 'status'], 'bill_run_preflight_code_status_idx');
            $table->index(['entity_type', 'entity_id'], 'bill_run_preflight_entity_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_run_preflight_checks');
    }
};
