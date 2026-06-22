<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('todo_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_key', 80)->nullable()->index();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('category', 60)->nullable()->index();
            $table->string('status', 30)->default('open')->index();
            $table->string('priority', 30)->default('medium')->index();
            $table->string('month_cycle', 20)->nullable()->index();
            $table->date('due_date')->nullable()->index();
            $table->unsignedBigInteger('assigned_to_user_id')->nullable()->index();
            $table->unsignedBigInteger('created_by_user_id')->nullable()->index();
            $table->unsignedBigInteger('updated_by_user_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['status', 'priority']);
            $table->index(['month_cycle', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('todo_tasks');
    }
};
