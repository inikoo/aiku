<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 15:06:23 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Procurement\SupplierProduct\SupplierProductQuantityStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductTradeUnitCompositionEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('trade_unit_composition')->default(SupplierProductTradeUnitCompositionEnum::MATCH->value)->nullable();

            $table->string('slug')->nullable()->index();

            $table->unsignedInteger('current_historic_supplier_product_id')->index()->nullable();

            $table->unsignedInteger('supplier_id')->nullable();
            $table->foreign('supplier_id')->references('id')->on('suppliers');

            $table->unsignedSmallInteger('agent_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('agents');


            $table->string('state')->nullable()->index();
            $table->boolean('status')->nullable()->index();

            $table->string('stock_quantity_status')->default(SupplierProductQuantityStatusEnum::NO_APPLICABLE->value)->nullable()->index();


            $table->string('code')->index();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();

            $table->unsignedDecimal('cost', 18, 4)->comment('unit cost');
            $table->unsignedInteger('units_per_pack')->nullable()->comment('units per pack');
            $table->unsignedInteger('units_per_carton')->nullable()->comment('units per carton');


            $table->jsonb('settings');
            $table->jsonb('shared_data');
            $table->jsonb('tenant_data');

            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedSmallInteger('central_supplier_product_id')->nullable();
            //$table->foreign('central_supplier_product_id')->references('id')->on('central.central_supplier_products');

            $table->unsignedInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('supplier_products');
    }
};
