<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Catalogue\Product\ProductStateEnum;
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
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->groupOrgRelationship($table);
            $table->unsignedSmallInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedInteger('asset_id')->nullable();
            $table->foreign('asset_id')->references('id')->on('assets');
            $table->unsignedSmallInteger('family_id')->nullable();
            $table->unsignedSmallInteger('department_id')->nullable();

            $table->boolean('status')->default(false)->index();
            $table->string('state')->default(ProductStateEnum::IN_PROCESS)->index();

            $table=$this->assetModelFields($table);

            $table->string('barcode')->index()->nullable()->comment('mirror from trade_unit');
            $table->decimal('rrp', 12, 3)->nullable()->comment('RRP per outer');
            $table->unsignedInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media')->onDelete('cascade');
            $table->string('unit_relationship_type')->nullable()->index();
            $table->unsignedInteger('available_quantity')->default(0)->nullable()->comment('outer available quantity for sale');

            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('source_id')->nullable()->unique();
            $table->string('historic_source_id')->nullable()->index();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
