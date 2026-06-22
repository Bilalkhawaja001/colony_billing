<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_runs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('run_uuid')->unique();
            $table->string('source', 24)->default('v2');
            $table->string('bill_type', 32)->default('electric_v1');
            $table->string('month_cycle', 7);
            $table->date('cycle_start_date');
            $table->date('cycle_end_date');
            $table->unsignedSmallInteger('cycle_days');
            $table->string('scope_type', 32)->default('FULL_ELIGIBLE');
            $table->char('scope_hash', 64);
            $table->longText('scope_payload_json')->nullable();
            $table->string('committed_scope_key', 255)->nullable()->unique();
            $table->string('status', 32)->default('DRAFT');
            $table->boolean('approval_required')->default(false);
            $table->foreignId('corrects_run_id')->nullable()->constrained('bill_runs')->restrictOnDelete();
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->unsignedInteger('snapshot_version')->default(0);
            $table->longText('readiness_result_json')->nullable();
            $table->longText('summary_json')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->unsignedBigInteger('submitted_by_user_id')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('generated_by_user_id')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->unsignedBigInteger('published_by_user_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('closed_by_user_id')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('voided_by_user_id')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->string('void_reason', 500)->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamps();
            $table->index(['month_cycle', 'bill_type'], 'bill_runs_period_type_idx');
            $table->index(['cycle_start_date', 'cycle_end_date'], 'bill_runs_cycle_idx');
            $table->index(['status', 'month_cycle'], 'bill_runs_status_period_idx');
            $table->index(['scope_type', 'scope_hash'], 'bill_runs_scope_idx');
            $table->index('corrects_run_id', 'bill_runs_corrects_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_runs');
    }
};
