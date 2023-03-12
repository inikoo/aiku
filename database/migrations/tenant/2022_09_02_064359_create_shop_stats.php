<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:44:17 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Sales\Customer\CustomerStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('shop_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');

            $table->unsignedInteger('number_customers')->default(0);

            foreach (CustomerStateEnum::asDatabaseColumns() as $customerState) {
                $table->unsignedInteger("number_customers_state_$customerState")->default(0);
            }
            $customerNumberInvoicesStates = ['none', 'one', 'many'];
            foreach ($customerNumberInvoicesStates as $customerNumberInvoicesState) {
                $table->unsignedInteger('number_customers_trade_state_'.$customerNumberInvoicesState)->default(0);
            }

            $table->unsignedInteger('number_departments')->default(0);
            $departmentStates = ['in-process', 'active', 'discontinuing', 'discontinued'];
            foreach ($departmentStates as $departmentState) {
                $table->unsignedInteger('number_departments_state_'.str_replace('-', '_', $departmentState))->default(0);
            }

            $table->unsignedInteger('number_families')->default(0);
            $familyStates = ['in-process', 'active', 'discontinuing', 'discontinued'];
            foreach ($familyStates as $familyState) {
                $table->unsignedInteger('number_families_state_'.str_replace('-', '_', $familyState))->default(0);
            }
            $table->unsignedInteger('number_orphan_families')->default(0);

            $table->unsignedInteger('number_products')->default(0);
            $productStates = ['in-process', 'active', 'discontinuing', 'discontinued'];
            foreach ($productStates as $productState) {
                $table->unsignedInteger('number_products_state_'.str_replace('-', '_', $productState))->default(0);
            }


            $table->unsignedInteger('number_orders')->default(0);
            $orderStates = ['in-basket', 'in-process', 'in-warehouse', 'packed', 'finalised', 'dispatched', 'returned', 'cancelled'];
            foreach ($orderStates as $orderState) {
                $table->unsignedInteger('number_orders_state_'.str_replace('-', '_', $orderState))->default(0);
            }

            $table->unsignedInteger('number_deliveries')->default(0);
            $table->unsignedInteger('number_deliveries_type_order')->default(0);
            $table->unsignedInteger('number_deliveries_type_replacement')->default(0);


            $deliveryStates = [
                'submitted',
                'picker-assigned',
                'picking',
                'picked',

                'packing',
                'packed',
                'finalised',
                'dispatched',
            ];

            foreach ($deliveryStates as $deliveryState) {
                $table->unsignedInteger('number_deliveries_state_'.str_replace('-', '_', $deliveryState))->default(0);
            }

            foreach ($deliveryStates as $deliveryState) {
                $table->unsignedInteger('number_deliveries_cancelled_at_state_'.str_replace('-', '_', $deliveryState))->default(0);
            }

            $table->unsignedInteger('number_invoices')->default(0);
            $table->unsignedInteger('number_invoices_type_invoice')->default(0);
            $table->unsignedInteger('number_invoices_type_refund')->default(0);

            $table->unsignedInteger('number_payment_service_providers')->default(0);
            $table->unsignedInteger('number_payment_accounts')->default(0);
            $table->unsignedInteger('number_payments')->default(0);


            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('shop_stats');
    }
};
