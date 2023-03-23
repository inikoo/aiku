<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 17:19:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant\Hydrators;

use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;
use App\Models\Central\Tenant;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
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
            'number_suppliers'        => Supplier::where('type', 'supplier')->count(),
            'number_active_suppliers' => Supplier::where('type', 'supplier')->where('status', true)->count(),

            'number_agents'               => Agent::count(),
            'number_active_agents'        => Agent::where('status', true)->count(),
            'number_active_tenant_agents' => Agent::where('status', true)->whereNull('central_agent_id')->count(),
            'number_active_global_agents' => Agent::where('status', true)->whereNotNull('central_agent_id')->count(),

            'number_products' => SupplierProduct::count(),

        ];


        $stateCounts = SupplierProduct::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        foreach (SupplierProductStateEnum::cases() as $productState) {
            $stats['number_products_state_'.$productState->snake()] = Arr::get($stateCounts, $productState->value, 0);
        }

        $stockQuantityStatusCounts = SupplierProduct::selectRaw('stock_quantity_status, count(*) as total')
            ->groupBy('stock_quantity_status')
            ->pluck('total', 'stock_quantity_status')->all();

        foreach (SupplierProductStateEnum::cases() as $stockQuantityStatus) {
            $stats['number_products_stock_quantity_status_'.$stockQuantityStatus->snake()] = Arr::get($stockQuantityStatusCounts, $stockQuantityStatus->value, 0);
        }


        $tenant->procurementStats->update($stats);
    }
}
