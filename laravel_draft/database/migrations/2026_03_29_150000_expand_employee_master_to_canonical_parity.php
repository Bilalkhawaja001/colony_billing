<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees_master', function (Blueprint $table) {
            $table->string('father_name')->nullable()->after('name');
            $table->string('cnic_no')->nullable()->after('father_name');
            $table->string('mobile_no')->nullable()->after('cnic_no');
            $table->string('section')->nullable()->after('department');
            $table->string('sub_section')->nullable()->after('section');
            $table->string('employee_type')->nullable()->after('designation');
            $table->date('join_date')->nullable()->after('employee_type');
            $table->date('leave_date')->nullable()->after('join_date');
            $table->string('shared_room')->nullable()->after('room_no');

            $table->string('iron_cot')->nullable();
            $table->string('single_bed')->nullable();
            $table->string('double_bed')->nullable();
            $table->string('mattress')->nullable();
            $table->string('sofa_set')->nullable();
            $table->string('bed_sheet')->nullable();
            $table->string('wardrobe')->nullable();
            $table->string('centre_table')->nullable();
            $table->string('wooden_chair')->nullable();
            $table->string('dinning_table')->nullable();
            $table->string('dinning_chair')->nullable();
            $table->string('side_table')->nullable();
            $table->string('fridge')->nullable();
            $table->string('water_dispenser')->nullable();
            $table->string('washing_machine')->nullable();
            $table->string('air_cooler')->nullable();
            $table->string('ac')->nullable();
            $table->string('led')->nullable();
            $table->string('gyser')->nullable();
            $table->string('electric_kettle')->nullable();
            $table->string('wifi_rtr')->nullable();
            $table->string('water_bottle')->nullable();
            $table->string('lpg_cylinder')->nullable();
            $table->string('gas_stove')->nullable();
            $table->string('crockery')->nullable();
            $table->string('kitchen_cabinet')->nullable();
            $table->string('mug')->nullable();
            $table->string('bucket')->nullable();
            $table->string('mirror')->nullable();
            $table->string('dustbin')->nullable();
            $table->text('remarks')->nullable();

            $table->index('cnic_no');
        });

        Schema::table('employees_registry', function (Blueprint $table) {
            $table->string('section')->nullable()->after('department');
            $table->string('sub_section')->nullable()->after('section');
            $table->string('employee_type')->nullable()->after('designation');
            $table->date('join_date')->nullable()->after('employee_type');
            $table->date('leave_date')->nullable()->after('join_date');
            $table->string('shared_room')->nullable()->after('room_no');

            $table->string('iron_cot')->nullable();
            $table->string('single_bed')->nullable();
            $table->string('double_bed')->nullable();
            $table->string('mattress')->nullable();
            $table->string('sofa_set')->nullable();
            $table->string('bed_sheet')->nullable();
            $table->string('wardrobe')->nullable();
            $table->string('centre_table')->nullable();
            $table->string('wooden_chair')->nullable();
            $table->string('dinning_table')->nullable();
            $table->string('dinning_chair')->nullable();
            $table->string('side_table')->nullable();
            $table->string('fridge')->nullable();
            $table->string('water_dispenser')->nullable();
            $table->string('washing_machine')->nullable();
            $table->string('air_cooler')->nullable();
            $table->string('ac')->nullable();
            $table->string('led')->nullable();
            $table->string('gyser')->nullable();
            $table->string('electric_kettle')->nullable();
            $table->string('wifi_rtr')->nullable();
            $table->string('water_bottle')->nullable();
            $table->string('lpg_cylinder')->nullable();
            $table->string('gas_stove')->nullable();
            $table->string('crockery')->nullable();
            $table->string('kitchen_cabinet')->nullable();
            $table->string('mug')->nullable();
            $table->string('bucket')->nullable();
            $table->string('mirror')->nullable();
            $table->string('dustbin')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('employees_master', function (Blueprint $table) {
            $table->dropIndex(['cnic_no']);
            $table->dropColumn([
                'father_name','cnic_no','mobile_no','section','sub_section','employee_type','join_date','leave_date','shared_room',
                'iron_cot','single_bed','double_bed','mattress','sofa_set','bed_sheet','wardrobe','centre_table','wooden_chair',
                'dinning_table','dinning_chair','side_table','fridge','water_dispenser','washing_machine','air_cooler','ac','led',
                'gyser','electric_kettle','wifi_rtr','water_bottle','lpg_cylinder','gas_stove','crockery','kitchen_cabinet','mug',
                'bucket','mirror','dustbin','remarks'
            ]);
        });

        Schema::table('employees_registry', function (Blueprint $table) {
            $table->dropColumn([
                'section','sub_section','employee_type','join_date','leave_date','shared_room',
                'iron_cot','single_bed','double_bed','mattress','sofa_set','bed_sheet','wardrobe','centre_table','wooden_chair',
                'dinning_table','dinning_chair','side_table','fridge','water_dispenser','washing_machine','air_cooler','ac','led',
                'gyser','electric_kettle','wifi_rtr','water_bottle','lpg_cylinder','gas_stove','crockery','kitchen_cabinet','mug',
                'bucket','mirror','dustbin'
            ]);
        });
    }
};
