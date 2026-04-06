<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('auth_password_reset_otp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('auth_users')->cascadeOnDelete();
            $table->string('otp_hash');
            $table->dateTime('expires_at');
            $table->integer('attempts')->default(0);
            $table->dateTime('used_at')->nullable();
            $table->dateTime('last_sent_at')->nullable();
            $table->index(['user_id', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_password_reset_otp');
    }
};
