<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 10:58:51 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('supplier_stats', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedMediumInteger('supplier_id')->index();
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->unsignedSmallInteger('number_products')->default(0);


            $productStates = ['in-process', 'active', 'no-available', 'discontinuing', 'discontinued'];
            foreach ($productStates as $productState) {
                $table->unsignedBigInteger('number_products_state_'.str_replace('-', '_', $productState))->default(0);
            }

            $productStockQuantityStatuses = ['surplus', 'optimal', 'low', 'critical', 'out-of-stock', 'no_applicable'];
            foreach ($productStockQuantityStatuses as $productStockQuantityStatus) {
                $table->unsignedBigInteger('number_products_stock_quantity_status_'.str_replace('-', '_', $productStockQuantityStatus))->default(0);
            }

            $table->unsignedSmallInteger('number_purchase_orders')->default(0);
            $purchaseOrderStates = ['in-process', 'submitted', 'confirmed', 'dispatched', 'delivered', 'cancelled'];
            foreach ($purchaseOrderStates as $purchaseOrderState) {
                $table->unsignedBigInteger('number_purchase_orders_state_'.str_replace('-', '_', $purchaseOrderState))->default(0);
            }
            $table->unsignedSmallInteger('number_deliveries')->default(0);


            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('supplier_stats');
    }
};
