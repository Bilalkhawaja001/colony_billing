<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('util_house_allowance_correction_snapshots')) {
            return;
        }

        Schema::create('util_house_allowance_correction_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('month_cycle', 20)->index();
            $table->string('correlation_id', 255)->unique();
            $table->string('action', 50)->default('APPLY');
            $table->string('actor_user_id', 255)->nullable();
            $table->text('rule');
            $table->longText('before_json');
            $table->longText('after_json');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('util_house_allowance_correction_snapshots');
    }
};
