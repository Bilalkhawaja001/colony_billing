<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('auth_audit_log', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->string('username_hint')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('outcome');
            $table->json('details_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_audit_log');
    }
};
