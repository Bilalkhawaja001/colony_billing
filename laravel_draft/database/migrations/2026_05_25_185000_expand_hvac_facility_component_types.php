<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('facility_component_types')
            || !Schema::hasTable('facility_work_categories')
            || !Schema::hasTable('facility_work_category_component_types')) {
            throw new \RuntimeException('Required Facilities master or mapping table is missing.');
        }

        $newHvacItems = [
            'Ventilation Fan',
            'Industrial Exhaust Fan',
            'Fresh Air Fan',
            'Air Vent / Diffuser',
            'Split AC Unit',
            'AC Indoor Unit',
            'AC Outdoor Unit',
            'AC Drain Pipe',
            'AC Copper Pipe / Refrigerant Line',
            'AC Electrical Wiring / Breaker',
            'AC Remote / Controller',
            'AC Mounting Bracket',
            'Air Cooler',
            'Air Cooler Motor / Fan',
            'Air Cooler Water Pump',
            'Chiller Unit',
            'Cooling Tower',
            'Cooling Water Pump',
            'Air Duct',
            'Duct Grill / Vent Cover',
            'Air Filter',
            'Blower Fan',
            'Refrigerant Gas / AC Gas',
            'R-22 Refrigerant Gas',
            'R-32 Refrigerant Gas',
            'R-410A Refrigerant Gas',
            'R-134a Refrigerant Gas',
        ];

        $expectedHvacItems = [
            'Exhaust Fan',
            'Exhaust Fan Grill',
            'Exhaust Fan Duct',
            'Ventilator Window',
            ...$newHvacItems,
        ];

        DB::transaction(function () use ($newHvacItems, $expectedHvacItems): void {
            $category = DB::table('facility_work_categories')
                ->where('name', 'HVAC / Ventilation / Cooling')
                ->where('is_active', 1)
                ->first();

            if (!$category) {
                throw new \RuntimeException('Active HVAC work category is missing.');
            }

            $nextSortOrder = (int) DB::table('facility_component_types')->max('sort_order');

            foreach ($newHvacItems as $name) {
                $existing = DB::table('facility_component_types')
                    ->where('name', $name)
                    ->first();

                if ($existing && (int) $existing->is_active !== 1) {
                    throw new \RuntimeException('Existing HVAC component is inactive: '.$name);
                }

                if (!$existing) {
                    $nextSortOrder++;

                    DB::table('facility_component_types')->insert([
                        'name' => $name,
                        'is_active' => 1,
                        'sort_order' => $nextSortOrder,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $componentRows = DB::table('facility_component_types')
                ->whereIn('name', $expectedHvacItems)
                ->where('is_active', 1)
                ->get(['id', 'name']);

            if ($componentRows->count() !== 31) {
                throw new \RuntimeException('Approved HVAC component master count is not exactly 31.');
            }

            foreach ($componentRows as $component) {
                DB::table('facility_work_category_component_types')->insertOrIgnore([
                    'work_category_id' => $category->id,
                    'component_type_id' => $component->id,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $mappedCount = DB::table('facility_work_category_component_types as map')
                ->join('facility_component_types as ct', 'ct.id', '=', 'map.component_type_id')
                ->where('map.work_category_id', $category->id)
                ->where('map.is_active', 1)
                ->where('ct.is_active', 1)
                ->whereIn('ct.name', $expectedHvacItems)
                ->count();

            if ($mappedCount !== 31) {
                throw new \RuntimeException('Approved HVAC category mapping count is not exactly 31.');
            }
        });
    }

    public function down(): void
    {
    }
};
