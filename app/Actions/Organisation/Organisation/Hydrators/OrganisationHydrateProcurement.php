<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Organisation\Hydrators;

use App\Enums\Procurement\AgentOrganisation\AgentOrganisationStatusEnum;
use App\Enums\Procurement\PurchaseOrderItem\PurchaseOrderItemStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductQuantityStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;
use App\Enums\Procurement\SupplierOrganisation\SupplierOrganisationStatusEnum;
use App\Models\Procurement\AgentOrganisation;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\SupplierProduct;
use App\Models\Procurement\SupplierProductOrganisation;
use App\Models\Procurement\SupplierOrganisation;
use App\Models\Organisation\Organisation;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateProcurement implements ShouldBeUnique
{
    use AsAction;
    use HasOrganisationHydrate;

    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_suppliers'                    => $organisation->suppliers()->where('suppliers.status', true)->whereNull('supplier_tenant.agent_id')->count(),
            'number_archived_suppliers'           => $organisation->suppliers()->where('suppliers.status', false)->whereNull('supplier_tenant.agent_id')->count(),
            'number_suppliers_in_agents'          => $organisation->suppliers()->where('suppliers.status', true)->whereNotNull('supplier_tenant.agent_id')->count(),
            'number_archived_suppliers_in_agents' => $organisation->suppliers()->where('suppliers.status', false)->whereNotNull('supplier_tenant.agent_id')->count(),
            'number_agents'                       => $organisation->agents()->where('agents.status', true)->count(),
            'number_archived_agents'              => $organisation->agents()->where('agents.status', false)->count(),

            'supplier_products_count'  => SupplierProductOrganisation::where('tenant_id', $organisation->id)->count(),
            'number_supplier_products' => SupplierProductOrganisation::where('tenant_id', $organisation->id)
                ->leftJoin('supplier_products', 'supplier_products.id', '=', 'supplier_product_tenant.supplier_product_id')
                ->where('supplier_products.state', '!=', SupplierProductStateEnum::DISCONTINUED)
                ->count(),

            'number_purchase_orders' => PurchaseOrder::count()
        ];


        $statusCounts = AgentOrganisation::selectRaw('status, count(*) as total')->where('tenant_id', $organisation->id)
            ->groupBy('status')
            ->pluck('total', 'status')->all();
        foreach (AgentOrganisationStatusEnum::cases() as $agentStatus) {
            $stats['number_agents_status_'.$agentStatus->snake()] = Arr::get($statusCounts, $agentStatus->value, 0);
        }

        $statusCounts = SupplierOrganisation::selectRaw('status, count(*) as total')->where('tenant_id', $organisation->id)
            ->groupBy('status')
            ->pluck('total', 'status')->all();
        foreach (SupplierOrganisationStatusEnum::cases() as $supplierStatus) {
            $stats['number_suppliers_status_'.$supplierStatus->snake()] = Arr::get($statusCounts, $supplierStatus->value, 0);
        }

        $stateCounts = SupplierProduct::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        foreach (SupplierProductStateEnum::cases() as $productState) {
            $stats['number_supplier_products_state_'.$productState->snake()] = Arr::get($stateCounts, $productState->value, 0);
        }

        $stockQuantityStatusCounts = SupplierProduct::selectRaw('stock_quantity_status, count(*) as total')
            ->groupBy('stock_quantity_status')
            ->pluck('total', 'stock_quantity_status')->all();

        foreach (SupplierProductQuantityStatusEnum::cases() as $stockQuantityStatus) {
            $stats['number_supplier_products_stock_quantity_status_'.$stockQuantityStatus->snake()] = Arr::get($stockQuantityStatusCounts, $stockQuantityStatus->value, 0);
        }

        $purchaseOrderStatusCounts = PurchaseOrder::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')->all();

        foreach (PurchaseOrderItemStatusEnum::cases() as $purchaseOrderStatusEnum) {
            $stats['number_purchase_orders_status_'.$purchaseOrderStatusEnum->snake()] = Arr::get($purchaseOrderStatusCounts, $purchaseOrderStatusEnum->value, 0);
        }

        $organisation->procurementStats->update($stats);
    }
}
