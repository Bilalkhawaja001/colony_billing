<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('portal_user_id')->nullable()->index();
            $table->unsignedBigInteger('billing_user_id')->nullable()->index();
            $table->string('module_key', 40)->default('BILLING')->index();
            $table->string('action', 120);
            $table->string('record_type', 120)->nullable();
            $table->string('record_id', 120)->nullable();
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['module_key', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_activity_logs');
    }
};
