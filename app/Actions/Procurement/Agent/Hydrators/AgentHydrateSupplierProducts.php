<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 00:51:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent\Hydrators;

use App\Enums\Procurement\SupplierProduct\SupplierProductQuantityStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;
use App\Models\Procurement\Agent;
use App\Models\Procurement\SupplierProduct;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class AgentHydrateSupplierProducts implements ShouldBeUnique
{
    use AsAction;


    public function handle(Agent $agent): void
    {
        $stats         = [
            'number_supplier_products' => $agent->products->count(),
        ];

        $stateCounts   =SupplierProduct::where('agent_id', $agent->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        foreach (SupplierProductStateEnum::cases() as $productState) {
            $stats['number_supplier_products_state_'.$productState->snake()] = Arr::get($stateCounts, $productState->value, 0);
        }

        $stockQuantityStatusCounts = SupplierProduct::where('agent_id', $agent->id)
            ->selectRaw('stock_quantity_status, count(*) as total')
            ->groupBy('stock_quantity_status')
            ->pluck('total', 'stock_quantity_status')->all();

        foreach (SupplierProductQuantityStatusEnum::cases() as $stockQuantityStatus) {
            $stats['number_supplier_products_stock_quantity_status_'.$stockQuantityStatus->snake()] = Arr::get($stockQuantityStatusCounts, $stockQuantityStatus->value, 0);
        }
        $agent->stats->update($stats);
    }


    public function getJobUniqueId(Agent $agent): int
    {
        return $agent->id;
    }
}
