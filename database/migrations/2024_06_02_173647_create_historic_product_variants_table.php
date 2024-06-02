<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 19:43:44 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasAssetModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasAssetModel;
    public function up(): void
    {
        Schema::create('historic_product_variants', function (Blueprint $table) {
            $table=$this->productFields($table);

            $table->unsignedInteger('product_variant_id')->nullable();
            $table->foreign('product_variant_id')->references('id')->on('product_variants');

            $table->string('code')->index()->collation('und_ns');
            $table->string('name', 255)->nullable();
            $table->decimal('price', 18)->nullable();
            $table->decimal('ratio', 4);
            $table->string('unit');
            $table->unsignedSmallInteger('units')->default(1);
            $table->unsignedSmallInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->timestampsTz();
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->foreign('current_historic_product_variant_id')->references('id')->on('historic_product_variants');
        });
    }



    public function down(): void
    {
        Schema::table('outers', function (Blueprint $table) {
            $table->dropForeign('current_historic_product_variant_id_foreign');
        });
        Schema::dropIfExists('historic_product_variants');
    }
};
