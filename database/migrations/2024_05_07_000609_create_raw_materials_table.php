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
            $table->unsignedMediumInteger('key');
            $table->string('type')->default(RawMaterialTypeEnum::PART->value);
            $table->unsignedMediumInteger('type_key');
            $table->string('state')->default(RawMaterialStateEnum::IN_PROCESS->value);
            $table->unsignedMediumInteger('production_supplier_key');
            $table->dateTimeTz('creation_date');
            $table->string('code', 64);
            $table->string('description', 255);
            $table->decimal('part_unit_ratio', 20, 6);
            $table->string('unit')->default(RawMaterialUnitEnum::UNIT->value);
            $table->string('unit_label', 64);
            $table->decimal('unit_cost', 18, 3);
            $table->decimal('stock', 18, 3);
            $table->string('stock_status')->default(RawMaterialStockStatusEnum::UNLIMITED->value);
            $table->unsignedMediumInteger('production_parts_number');
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('raw_materials');
    }
};
