<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_portal_user_links', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('portal_user_id');
            $table->foreignId('billing_user_id')->constrained('auth_users')->cascadeOnDelete();
            $table->string('module_key', 40)->default('BILLING');
            $table->string('billing_role', 80)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['portal_user_id', 'module_key']);
            $table->index(['billing_user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_portal_user_links');
    }
};
