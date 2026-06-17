<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('facility_work_category_component_types')) {
            Schema::create('facility_work_category_component_types', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('work_category_id');
                $table->unsignedBigInteger('component_type_id');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(
                    ['work_category_id', 'component_type_id'],
                    'fwcct_category_component_unique'
                );

                $table->foreign('work_category_id', 'fwcct_category_fk')
                    ->references('id')
                    ->on('facility_work_categories')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->foreign('component_type_id', 'fwcct_component_fk')
                    ->references('id')
                    ->on('facility_component_types')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_work_category_component_types');
    }
};
