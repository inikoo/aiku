<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 04:45:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Procurement\OrgSupplierProduct\OrgSupplierProductStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderDeliveryStatusEnum;
use App\Enums\Procurement\StockDelivery\StockDeliveryStateEnum;
use App\Enums\Procurement\StockDelivery\StockDeliveryStatusEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasProcurementStats
{
    public function procurementStatsFields(Blueprint $table): Blueprint
    {

        $table = $this->orgAgentStats($table);
        $table = $this->orgSuppliersStats($table);
        $table = $this->orgSupplierProductsStats($table);
        $table = $this->purchaseOrdersStats($table);
        $table = $this->stockDeliveriesStats($table);
        $table = $this->purchaseOrderTransactionsStats($table);

        return $this->stockDeliveryItemsStats($table);
    }

    public function orgAgentStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_org_agents')->default(0)->comment('Total number agens active+archived');
        $table->unsignedInteger('number_active_org_agents')->default(0)->comment('Active agents, status=true');
        $table->unsignedInteger('number_archived_org_agents')->default(0)->comment('Archived agents, status=false');

        return $table;
    }


    public function orgSuppliersStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_org_suppliers')->default(0)->comment('Active + Archived  suppliers');
        $table->unsignedInteger('number_active_org_suppliers')->default(0)->comment('Active suppliers, status=true');
        $table->unsignedInteger('number_archived_org_suppliers')->default(0)->comment('Archived suppliers status=false');

        if ($table->getTable() != 'agent_stats') {
            $table->unsignedInteger('number_independent_org_suppliers')->default(0)->comment('Active + Archived no agent suppliers');
            $table->unsignedInteger('number_active_independent_org_suppliers')->default(0)->comment('Active no agent suppliers, status=true');
            $table->unsignedInteger('number_archived_independent_org_suppliers')->default(0)->comment('Archived no agent suppliers status=false');

            $table->unsignedInteger('number_org_suppliers_in_agents')->default(0)->comment('Active + Archived suppliers');
            $table->unsignedInteger('number_active_org_suppliers_in_agents')->default(0)->comment('Active suppliers, status=true');
            $table->unsignedInteger('number_archived_org_suppliers_in_agents')->default(0)->comment('Archived suppliers status=false');
        }

        return $table;
    }


    public function orgSupplierProductsStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_org_supplier_products')->default(0);
        $table->unsignedInteger('number_current_org_supplier_products')->default(0)->comment('status=true');
        $table->unsignedInteger('number_available_org_supplier_products')->default(0);
        $table->unsignedInteger('number_no_available_org_supplier_products')->default(0)->comment('only for state=active|discontinuing');

        foreach (OrgSupplierProductStateEnum::cases() as $productState) {
            $table->unsignedInteger('number_org_supplier_products_state_'.$productState->snake())->default(0);
        }

        return $table;
    }

    public function purchaseOrdersStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_purchase_orders')->default(0);
        $table->unsignedInteger('number_purchase_orders_except_cancelled')->default(0)->comment('Number purchase orders (except cancelled and failed) ');
        $table->unsignedInteger('number_open_purchase_orders')->default(0)->comment('Number purchase orders (except creating, settled)');


        foreach (PurchaseOrderStateEnum::cases() as $purchaseOrderState) {
            $table->unsignedInteger('number_purchase_orders_state_'.$purchaseOrderState->snake())->default(0);
        }


        foreach (PurchaseOrderDeliveryStatusEnum::cases() as $purchaseOrderStatus) {
            $table->unsignedInteger('number_purchase_orders_delivery_status_'.$purchaseOrderStatus->snake())->default(0);
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
