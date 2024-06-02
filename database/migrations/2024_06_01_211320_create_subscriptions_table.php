<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 23:27:45 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Catalogue\Service\ServiceStateEnum;
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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table = $this->groupOrgRelationship($table);
            $table->unsignedSmallInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedInteger('asset_id')->nullable();
            $table->foreign('asset_id')->references('id')->on('assets');


            $table->boolean('status')->default(false)->index();
            $table->string('state')->default(ServiceStateEnum::IN_PROCESS)->index();

            $table=$this->assetModelFields($table);

            $table->timestampsTz();
            $table->softDeletes();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
