<?php

use App\Enums\Manufacturing\RawMaterial\RawMaterialStateEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStockStatusEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialUnitEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HasGroupOrganisationRelationship;
    public function up()

    {
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table=$this->groupOrgRelationship($table);
            $table->unsignedMediumInteger('raw_material_key');
            $table->string('raw_material_type')->default(RawMaterialTypeEnum::PART->value);
            $table->unsignedMediumInteger('raw_material_type_key');
            $table->string('raw_material_state')->default(RawMaterialStateEnum::IN_PROCESS->value);
            $table->unsignedMediumInteger('raw_material_production_supplier_key');
            $table->dateTime('raw_material_creation_date');
            $table->string('raw_material_code', 64);
            $table->string('raw_material_description', 255);
            $table->decimal('raw_material_part_raw_material_unit_ratio', 20, 6);
            $table->string('raw_material_unit')->default(RawMaterialUnitEnum::UNIT->value);
            $table->string('raw_material_unit_label', 64);
            $table->decimal('raw_material_unit_cost', 18, 3);
            $table->decimal('raw_material_stock', 18, 3);
            $table->string('raw_material_stock_status')->default(RawMaterialStockStatusEnum::UNLIMITED->value);
            $table->unsignedMediumInteger('raw_material_production_parts_number');
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('raw_materials');
    }
};
