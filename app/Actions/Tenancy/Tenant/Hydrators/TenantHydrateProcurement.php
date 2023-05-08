<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant\Hydrators;

use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductQuantityStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\SupplierProduct;
use App\Models\SupplierProductTenant;
use App\Models\Tenancy\Tenant;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class TenantHydrateProcurement implements ShouldBeUnique
{
    use AsAction;
    use HasTenantHydrate;

    public function handle(Tenant $tenant): void
    {
        $stats = [
            'suppliers_count'          => $tenant->suppliers()->count(),
            'number_suppliers'         => $tenant->suppliers()->where('status', true)->count(),
            'agents_count'             => $tenant->agents()->count(),
            'number_agents'            => $tenant->agents()->where('status', true)->count(),

            'supplier_products_count'  => SupplierProductTenant::where('tenant_id', $tenant->id)->count(),
            'number_supplier_products' => SupplierProductTenant::where('tenant_id', $tenant->id)
                ->leftJoin('supplier_products', 'supplier_products.id', '=', 'supplier_product_tenant.supplier_product_id')
                ->where('supplier_products.state', '!=', SupplierProductStateEnum::DISCONTINUED)
                ->count(),

            'number_purchase_orders' => PurchaseOrder::count()
        ];



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

        foreach (PurchaseOrderStatusEnum::cases() as $purchaseOrderStatusEnum) {
            $stats['number_purchase_orders_status_'.$purchaseOrderStatusEnum->snake()] = Arr::get($purchaseOrderStatusCounts, $purchaseOrderStatusEnum->value, 0);
        }

        $tenant->procurementStats->update($stats);
    }
}
