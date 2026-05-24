<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $approvedCategories = [
        ['Plumbing & Drainage Works', 'MAINTENANCE'],
        ['Electrical & Lighting Works', 'MAINTENANCE'],
        ['Civil & Masonry Works', 'MAINTENANCE'],
        ['Metalwork / Welding / Fabrication', 'MAINTENANCE'],
        ['Pole / Outdoor Structure Works', 'MAINTENANCE'],
        ['Carpentry / Furniture / Wood Works', 'MAINTENANCE'],
        ['Painting / Coating / Finishing', 'MAINTENANCE'],
        ['HVAC / Ventilation / Cooling', 'MAINTENANCE'],
        ['Water Supply / RO / Pump / Geyser', 'MAINTENANCE'],
        ['Washroom & Sanitation Facilities', 'MAINTENANCE'],
        ['Cleaning & Housekeeping Services', 'SERVICE'],
        ['Pest Control / Fumigation', 'SERVICE'],
        ['Road / Pavement / Drain / Ground Works', 'MAINTENANCE'],
        ['Roofing / Shade / Canopy Works', 'MAINTENANCE'],
        ['Doors / Gates / Locks / Access Works', 'MAINTENANCE'],
        ['Glass / Aluminium / Partition Works', 'MAINTENANCE'],
        ['Garden / Fountain / Irrigation Works', 'MAINTENANCE'],
        ['Fire & Safety Facility Works', 'SAFETY'],
        ['Signage / Branding / Marking Works', 'MAINTENANCE'],
        ['Moving / Installation / Dismantling Works', 'MAINTENANCE'],
        ['General Facility Maintenance', 'MAINTENANCE'],
    ];

    private array $originalCategories = [
        ['Plumbing', 'MAINTENANCE'],
        ['Electrical', 'MAINTENANCE'],
        ['Civil Works', 'MAINTENANCE'],
        ['HVAC', 'MAINTENANCE'],
        ['Water Supply / RO / Geyser', 'MAINTENANCE'],
        ['Washroom & Sanitation', 'MAINTENANCE'],
        ['Market Maintenance', 'LOCATION'],
        ['Masjid Maintenance', 'LOCATION'],
        ['Colony / Guest House / Office', 'LOCATION'],
        ['Garden / Fountain / Common Area', 'LOCATION'],
        ['Fire & Safety Facility Works', 'SAFETY'],
        ['Daily Cleaning', 'SERVICE'],
        ['Pest Control / Fumigation', 'SERVICE'],
    ];

    public function up(): void
    {
        if (!Schema::hasColumn('facility_service_requests', 'requester_employee_id')) {
            Schema::table('facility_service_requests', function (Blueprint $table): void {
                $table->string('requester_employee_id', 40)->nullable()->after('requested_at');
                $table->string('requester_name_snapshot', 180)->nullable()->after('requester_employee_id');
                $table->string('requester_designation_snapshot', 180)->nullable()->after('requester_name_snapshot');
                $table->string('requester_department_snapshot', 180)->nullable()->after('requester_designation_snapshot');
                $table->string('requester_section_snapshot', 180)->nullable()->after('requester_department_snapshot');
                $table->string('requester_sub_section_snapshot', 180)->nullable()->after('requester_section_snapshot');
                $table->string('requester_mobile_no_snapshot', 60)->nullable()->after('requester_sub_section_snapshot');
                $table->index('requester_employee_id', 'fsr_requester_employee_idx');
            });
        }

        DB::table('facility_work_categories')->update([
            'is_active' => 0,
            'updated_at' => now(),
        ]);

        foreach ($this->approvedCategories as $index => [$name, $group]) {
            $existing = DB::table('facility_work_categories')->where('name', $name)->first();

            if ($existing) {
                DB::table('facility_work_categories')->where('id', $existing->id)->update([
                    'category_group' => $group,
                    'is_active' => 1,
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('facility_work_categories')->insert([
                    'name' => $name,
                    'category_group' => $group,
                    'is_active' => 1,
                    'sort_order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->approvedCategories as [$name]) {
            DB::table('facility_work_categories')->where('name', $name)->update([
                'is_active' => 0,
                'updated_at' => now(),
            ]);
        }

        foreach ($this->originalCategories as $index => [$name, $group]) {
            $existing = DB::table('facility_work_categories')->where('name', $name)->first();

            if ($existing) {
                DB::table('facility_work_categories')->where('id', $existing->id)->update([
                    'category_group' => $group,
                    'is_active' => 1,
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('facility_work_categories')->insert([
                    'name' => $name,
                    'category_group' => $group,
                    'is_active' => 1,
                    'sort_order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasColumn('facility_service_requests', 'requester_employee_id')) {
            Schema::table('facility_service_requests', function (Blueprint $table): void {
                $table->dropIndex('fsr_requester_employee_idx');
                $table->dropColumn([
                    'requester_employee_id',
                    'requester_name_snapshot',
                    'requester_designation_snapshot',
                    'requester_department_snapshot',
                    'requester_section_snapshot',
                    'requester_sub_section_snapshot',
                    'requester_mobile_no_snapshot',
                ]);
            });
        }
    }
};
