<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:44:17 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('shop_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedBigInteger('number_customers')->default(0);
            $customerStates = ['in-process', 'active', 'losing', 'lost', 'registered'];
            foreach ($customerStates as $customerState) {
                $table->unsignedBigInteger('number_customers_state_'.str_replace('-', '_', $customerState))->default(0);
            }
            $customerNumberInvoicesStates = ['none', 'one', 'many'];
            foreach ($customerNumberInvoicesStates as $customerNumberInvoicesState) {
                $table->unsignedBigInteger('number_customers_trade_state_'.$customerNumberInvoicesState)->default(0);
            }

            $table->unsignedBigInteger('number_departments')->default(0);
            $departmentStates = ['creating', 'active', 'suspended', 'discontinuing', 'discontinued'];
            foreach ($departmentStates as $departmentState) {
                $table->unsignedBigInteger('number_departments_state_'.str_replace('-', '_', $departmentState))->default(0);
            }

            $table->unsignedBigInteger('number_families')->default(0);
            $familyStates = ['creating', 'active', 'suspended', 'discontinuing', 'discontinued'];
            foreach ($familyStates as $familyState) {
                $table->unsignedBigInteger('number_families_state_'.str_replace('-', '_', $familyState))->default(0);
            }
            $table->unsignedBigInteger('number_orphan_families')->default(0);

            $table->unsignedBigInteger('number_products')->default(0);
            $productStates = ['creating', 'active', 'suspended', 'discontinuing', 'discontinued'];
            foreach ($productStates as $productState) {
                $table->unsignedBigInteger('number_products_state_'.str_replace('-', '_', $productState))->default(0);
            }


            $table->unsignedBigInteger('number_orders')->default(0);
            $orderStates = ['in-basket', 'in-process', 'in-warehouse', 'packed', 'packed-done', 'dispatched', 'returned', 'cancelled'];
            foreach ($orderStates as $orderState) {
                $table->unsignedBigInteger('number_orders_state_'.str_replace('-', '_', $orderState))->default(0);
            }

            $table->unsignedBigInteger('number_deliveries')->default(0);
            $table->unsignedBigInteger('number_deliveries_type_order')->default(0);
            $table->unsignedBigInteger('number_deliveries_type_replacement')->default(0);

            $deliveryStates = [
                'ready-to-be-picked',
                'picker-assigned',
                'picking',
                'picked',
                'packing',
                'packed',
                'packed-done',
                'approved',
                'dispatched',
                'cancelled',
                'cancelled-to-restock',
            ];

            foreach ($deliveryStates as $deliveryState) {
                $table->unsignedBigInteger('number_deliveries_state_'.str_replace('-', '_', $deliveryState))->default(0);
            }


            $table->unsignedBigInteger('number_invoices')->default(0);
            $table->unsignedBigInteger('number_invoices_type_invoice')->default(0);
            $table->unsignedBigInteger('number_invoices_type_refund')->default(0);


            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('shop_stats');
    }
};
