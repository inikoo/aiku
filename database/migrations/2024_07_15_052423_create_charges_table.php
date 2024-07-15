<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 Jul 2024 13:35:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Catalogue\Charge\ChargeStateEnum;
use App\Stubs\Migrations\HasAssetModel;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;
    use HasAssetModel;
    public function up(): void
    {
        Schema::create('charges', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table = $this->groupOrgRelationship($table);
            $table->unsignedSmallInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedInteger('asset_id')->nullable();
            $table->foreign('asset_id')->references('id')->on('assets');
            $table->boolean('status')->default(false)->index();
            $table->string('state')->default(ChargeStateEnum::IN_PROCESS)->index();
            $table=$this->assetModelFields($table);
            $table->timestampsTz();
            $table->softDeletes();
            $table->string('source_id')->nullable()->unique();
            $table->string('historic_source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('charges');
    }
};
