<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('electric_active_days_monthly', function (Blueprint $table) {
            $table->id();
            $table->date('billing_month_date');
            $table->string('company_id')->index();
            $table->decimal('active_days', 14, 4)->default(0);
            $table->text('remarks')->nullable();
            $table->string('source_file')->nullable();
            $table->string('uploaded_by')->nullable();
            $table->timestamps();

            $table->unique(['billing_month_date', 'company_id'], 'uq_electric_active_days_month_company');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electric_active_days_monthly');
    }
};
