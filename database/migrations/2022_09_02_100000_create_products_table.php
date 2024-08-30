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

            $table=$this->productFields($table);
            $table->boolean('is_main')->default(true)->index();
            $table->boolean('status')->default(false)->index();
            $table->string('state')->default(ProductStateEnum::IN_PROCESS)->index();

            $table=$this->assetModelFields($table);

            $table->unsignedInteger('weight')->nullable()->comment('grams');
            $table->unsignedInteger('commercial_weight')->nullable()->comment('grams');

            $table->string('barcode')->index()->nullable()->comment('mirror from trade_unit');
            $table->decimal('rrp', 12, 3)->nullable()->comment('RRP per outer');
            $table->unsignedInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media')->onDelete('cascade');
            $table->string('unit_relationship_type')->nullable()->index();
            $table->unsignedInteger('available_quantity')->default(0)->nullable()->comment('outer available quantity for sale');

            //variants fields

            $table->decimal('variant_ratio', 9, 3)->default(1);
            $table->boolean('variant_is_visible')->default(true)->index();
            $table->unsignedInteger('main_product_id')->nullable()->index();


            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('source_id')->nullable()->unique();
            $table->string('historic_source_id')->nullable()->index();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('main_product_id')->references('id')->on('products');
        });
    }


    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('main_product_id_foreign');
        });
        Schema::dropIfExists('products');
    }
};
