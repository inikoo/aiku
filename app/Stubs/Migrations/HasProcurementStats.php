<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 04:45:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductQuantityStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasProcurementStats
{
    public function procurementStats(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('number_products')->default(0)->comment('all excluding discontinued');
        foreach (SupplierProductStateEnum::cases() as $productState) {
            $table->unsignedBigInteger('number_products_state_'.$productState->snake())->default(0);
        }

        foreach (SupplierProductQuantityStatusEnum::cases() as $productStockQuantityStatus) {
            $table->unsignedBigInteger('number_products_stock_quantity_status_'.$productStockQuantityStatus->snake())->default(0);
        }

        $table->unsignedInteger('number_purchase_orders')->default(0);

        foreach (PurchaseOrderStateEnum::cases() as $purchaseOrderState) {
            $table->unsignedInteger('number_purchase_orders_state_'.$purchaseOrderState->snake())->default(0);
        }


        foreach (PurchaseOrderStatusEnum::cases() as $purchaseOrderStatus) {
            $table->unsignedInteger('number_purchase_orders_status_'.$purchaseOrderStatus->snake())->default(0);
        }

        $table->unsignedInteger('number_deliveries')->default(0);

        return $table;
    }
}
