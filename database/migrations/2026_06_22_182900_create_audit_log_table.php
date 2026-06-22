<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('run_id')->nullable()->constrained('bill_runs')->restrictOnDelete();
            $table->string('action', 100);
            $table->string('entity_type', 80);
            $table->string('entity_id', 120)->nullable();
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->string('actor_username', 120)->nullable();
            $table->longText('before_json')->nullable();
            $table->longText('after_json')->nullable();
            $table->longText('meta_json')->nullable();
            $table->string('source_file_name', 255)->nullable();
            $table->char('upload_hash', 64)->nullable();
            $table->string('correlation_id', 80)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('session_id', 120)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['run_id', 'created_at'], 'audit_log_run_created_idx');
            $table->index(['actor_user_id', 'created_at'], 'audit_log_actor_created_idx');
            $table->index(['action', 'created_at'], 'audit_log_action_created_idx');
            $table->index(['entity_type', 'entity_id'], 'audit_log_entity_idx');
            $table->index('correlation_id', 'audit_log_correlation_idx');
        });

        DB::unprepared("DROP TRIGGER IF EXISTS audit_log_prevent_update");
        DB::unprepared("DROP TRIGGER IF EXISTS audit_log_prevent_delete");

        DB::unprepared(<<<'SQL'
CREATE TRIGGER audit_log_prevent_update
BEFORE UPDATE ON audit_log
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_log is append-only; UPDATE is blocked';
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER audit_log_prevent_delete
BEFORE DELETE ON audit_log
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_log is append-only; DELETE is blocked';
END
SQL);
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS audit_log_prevent_update");
        DB::unprepared("DROP TRIGGER IF EXISTS audit_log_prevent_delete");
        Schema::dropIfExists('audit_log');
    }
};
