<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hr_active_workbook_uploads')) {
            Schema::create('hr_active_workbook_uploads', function (Blueprint $table) {
                $table->id();
                $table->string('month_cycle', 20)->index();
                $table->string('original_file_name')->nullable();
                $table->string('stored_path')->nullable();
                $table->unsignedInteger('sheet_count')->default(0);
                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('imported_rows')->default(0);
                $table->longText('duplicate_company_ids')->nullable();
                $table->longText('summary_json')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('hr_active_employee_snapshots')) {
            Schema::create('hr_active_employee_snapshots', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('upload_id')->nullable()->index();
                $table->string('month_cycle', 20)->index();
                $table->string('company_id', 50)->index();
                $table->string('sheet_name')->nullable();
                $table->unsignedInteger('row_no')->default(0);
                $table->string('name')->nullable();
                $table->string('father_name')->nullable();
                $table->string('cnic_no')->nullable();
                $table->string('mobile_no')->nullable();
                $table->string('department')->nullable();
                $table->string('section')->nullable();
                $table->string('sub_section')->nullable();
                $table->string('designation')->nullable();
                $table->string('employee_type')->nullable();
                $table->string('colony_type')->nullable();
                $table->string('block_floor')->nullable();
                $table->string('room_no')->nullable();
                $table->string('shared_room')->nullable();
                $table->string('join_date')->nullable();
                $table->string('unit_id')->nullable();
                $table->longText('raw_json')->nullable();
                $table->timestamps();

                $table->unique(['month_cycle', 'company_id'], 'uq_hr_active_snapshot_month_company');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_active_employee_snapshots');
        Schema::dropIfExists('hr_active_workbook_uploads');
    }
};
