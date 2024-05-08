<?php

use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardAllowanceTypeEnum;
use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardTermsEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Google\Service\ManufacturerCenter\ManufacturersEmpty;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HasGroupOrganisationRelationship;
    public function up()
    {
        Schema::create('manufacture_tasks', function (Blueprint $table) {
            $table->id();
            $table=$this->groupOrgRelationship($table);
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('code', 16);
            $table->string('name', 255);
            $table->float('task_materials_cost');
            $table->float('task_energy_cost');
            $table->float('task_other_cost');
            $table->float('task_work_cost');
            $table->dateTimeTz('task_from');
            $table->dateTimeTz('task_to');
            $table->boolean('task_active');
            $table->float('task_lower_target');
            $table->float('task_upper_target');
            $table->string('operative_reward_terms')->default(ManufactureTaskOperativeRewardTermsEnum::ABOVE_LOWER_LIMIT->value);
            $table->string('operative_reward_allowance_type')->default(ManufactureTaskOperativeRewardAllowanceTypeEnum::OFFSET_SALARY->value);
            $table->double('operative_reward_amount')->unsigned();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('manufacture_tasks');
    }
};
