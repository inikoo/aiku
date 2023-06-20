<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:44:17 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Market\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Market\Family\FamilyStateEnum;
use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Sales\Customer\CustomerStateEnum;
use App\Enums\Sales\Customer\CustomerTradeStateEnum;
use App\Enums\Sales\Order\OrderStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shop_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');

            $table->unsignedInteger('number_customers')->default(0);

            foreach (CustomerStateEnum::cases() as $customerState) {
                $table->unsignedInteger("number_customers_state_{$customerState->snake()}")->default(0);
            }
            foreach (CustomerTradeStateEnum::cases() as $tradeState) {
                $table->unsignedInteger('number_customers_trade_state_'.$tradeState->snake())->default(0);
            }

            $table->unsignedInteger('number_departments')->default(0);

            foreach (ProductCategoryStateEnum::cases() as $departmentState) {
                $table->unsignedInteger('number_departments_state_'.$departmentState->snake())->default(0);
            }

            $table->unsignedInteger('number_families')->default(0);

            foreach (FamilyStateEnum::cases() as $familyState) {
                $table->unsignedInteger('number_families_state_'.$familyState->snake())->default(0);
            }
            $table->unsignedInteger('number_orphan_families')->default(0);

            $table->unsignedInteger('number_products')->default(0);
            foreach (ProductStateEnum::cases() as $productState) {
                $table->unsignedInteger('number_products_state_'.$productState->snake())->default(0);
            }


            $table->unsignedInteger('number_orders')->default(0);
            foreach (OrderStateEnum::cases() as $orderState) {
                $table->unsignedInteger('number_orders_state_'.$orderState->snake())->default(0);
            }

            $table->unsignedInteger('number_deliveries')->default(0);
            $table->unsignedInteger('number_deliveries_type_order')->default(0);
            $table->unsignedInteger('number_deliveries_type_replacement')->default(0);


            foreach (DeliveryNoteStateEnum::cases() as $deliveryState) {
                $table->unsignedInteger('number_deliveries_state_'.$deliveryState->snake())->default(0);
            }

            foreach (DeliveryNoteStateEnum::cases() as $deliveryState) {
                if ($deliveryState->value != 'cancelled') {
                    $table->unsignedInteger('number_deliveries_cancelled_at_state_'.$deliveryState->snake())->default(0);
                }
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


    public function down(): void
    {
        Schema::dropIfExists('shop_stats');
    }
};
