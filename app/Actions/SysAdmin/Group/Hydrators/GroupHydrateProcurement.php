<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:14:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Enums\Procurement\SupplierProduct\SupplierProductQuantityStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;
use App\Models\SysAdmin\Group;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateProcurement
{
    use AsAction;

    public function handle(Group $group): void
    {
        $stats = [
            'number_suppliers'          => Supplier::where('suppliers.status', true)->count(),
            'number_archived_suppliers' => Supplier::where('suppliers.status', false)->count(),
            'number_agents'             => Agent::where('agents.status', true)->count(),
            'number_archived_agents'    => Agent::where('agents.status', false)->count(),

            'supplier_products_count'  => SupplierProduct::count(),
            'number_supplier_products' => SupplierProduct::where('supplier_products.state', '!=', SupplierProductStateEnum::DISCONTINUED)
                ->count(),
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

        $group->procurementStats->update($stats);
    }




}
