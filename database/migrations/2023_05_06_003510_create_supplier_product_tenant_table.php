<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 May 2023 15:26:45 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('supplier_product_tenant', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('supplier_product_id');
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('public.tenants');
            $table->timestampsTz();
            $table->unsignedInteger('source_id')->index()->nullable();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('supplier_product_tenant');
    }
};
