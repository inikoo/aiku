<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 04:45:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Procurement\PurchaseOrderItem\PurchaseOrderItemStateEnum;
use App\Enums\Procurement\PurchaseOrderItem\PurchaseOrderItemStatusEnum;
use App\Enums\Procurement\StockDelivery\StockDeliveryStateEnum;
use App\Enums\Procurement\StockDelivery\StockDeliveryStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductQuantityStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;

use Illuminate\Database\Schema\Blueprint;

trait HasProcurementStats
{
    public function agentStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_agents')->default(0)->comment('Total number agens active+archived');
        $table->unsignedInteger('number_active_agents')->default(0)->comment('Active agents, status=true');
        $table->unsignedInteger('number_archived_agents')->default(0)->comment('Archived agents, status=false');

        return $table;
    }

    public function suppliersStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_suppliers')->default(0)->comment('Active + Archived  suppliers');
        $table->unsignedInteger('number_active_suppliers')->default(0)->comment('Active suppliers, status=true');
        $table->unsignedInteger('number_archived_suppliers')->default(0)->comment('Archived suppliers status=false');


        if($table->getTable()!='agent_stats') {
            $table->unsignedInteger('number_suppliers_in_agents')->default(0)->comment('Active + Archived suppliers');
            $table->unsignedInteger('number_active_suppliers_in_agents')->default(0)->comment('Active suppliers, status=true');
            $table->unsignedInteger('number_archived_suppliers_in_agents')->default(0)->comment('Archived suppliers status=false');
        }


        return $table;
    }

    public function supplierProductsStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_supplier_products')->default(0);
        $table->unsignedInteger('number_supplier_products_state_active_and_discontinuing')->default(0);


        foreach (SupplierProductStateEnum::cases() as $productState) {
            $table->unsignedInteger('number_supplier_products_state_'.$productState->snake())->default(0);
        }


        foreach (SupplierProductQuantityStatusEnum::cases() as $productStockQuantityStatus) {
            $table->unsignedInteger('number_supplier_products_stock_quantity_status_'.$productStockQuantityStatus->snake())->default(0);
        }


        return $table;
    }

    public function purchaseOrdersStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_purchase_orders')->default(0);
        $table->unsignedInteger('number_purchase_orders_except_cancelled')->default(0)->comment('Number purchase orders (except cancelled and failed) ');
        $table->unsignedInteger('number_open_purchase_orders')->default(0)->comment('Number purchase orders (except creating, settled)');


        foreach (PurchaseOrderItemStateEnum::cases() as $purchaseOrderState) {
            $table->unsignedInteger('number_purchase_orders_state_'.$purchaseOrderState->snake())->default(0);
        }


        foreach (PurchaseOrderItemStatusEnum::cases() as $purchaseOrderStatus) {
            $table->unsignedInteger('number_purchase_orders_status_'.$purchaseOrderStatus->snake())->default(0);
        }


        return $table;
    }

    public function stockDeliveriesStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_stock_deliveries')->default(0)->comment('Number supplier deliveries');
        $table->unsignedInteger('number_stock_deliveries_except_cancelled')->default(0)->comment('Number supplier deliveries');

        foreach (StockDeliveryStateEnum::cases() as $case) {
            $table->unsignedInteger('number_stock_deliveries_state_'.$case->snake())->default(0);
        }

        foreach (StockDeliveryStatusEnum::cases() as $case) {
            $table->unsignedInteger('number_stock_deliveries_status_'.$case->snake())->default(0);
        }

        return $table;
    }


}
