<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 12:09:04 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Catalogue\ProductVariant\ProductVariantStateEnum;
use App\Stubs\Migrations\HasAssetModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasAssetModel;
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table=$this->productFields($table);
            $table->boolean('is_main')->default(false);
            $table->decimal('ratio', 9, 3);

            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products');

            $table->boolean('status')->default(false)->index();
            $table->string('state')->default(ProductVariantStateEnum::IN_PROCESS)->index();

            $table->boolean('is_visible')->default(true)->index();

            $table->string('unit_relationship_type')->nullable()->index();

            $table->string('slug')->unique()->collation('und_ns');
            $table->string('code')->index()->collation('und_ns');
            $table->string('name', 255)->nullable();
            $table->decimal('price', 18)->nullable();
            $table->string('unit');
            $table->decimal('units', 9, 3)->default(1);

            $table->unsignedSmallInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencies');

            $table->unsignedInteger('current_historic_product_variant_id')->index()->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('source_id')->nullable()->unique();
            $table->string('historic_source_id')->nullable()->index();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('product_variant_id')->references('id')->on('product_variants');
        });


    }


    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('variant_id_foreign');
        });

        Schema::dropIfExists('product_variants');
    }
};
