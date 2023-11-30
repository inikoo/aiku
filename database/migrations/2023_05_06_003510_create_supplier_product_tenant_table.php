<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Nov 2023 23:23:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('supplier_product_organisation', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('supplier_product_id');
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products');
            $table->unsignedSmallInteger('organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->timestampsTz();
            $table->unsignedInteger('source_id')->index()->nullable();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('supplier_product_organisation');
    }
};
