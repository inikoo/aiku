<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:14:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Enums\Inventory\Stock\OrgStockQuantityStatusEnum;
use App\Enums\Inventory\Stock\OrgStockStateEnum;
use App\Enums\Inventory\StockFamily\StockFamilyStateEnum;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use App\Models\SysAdmin\Group;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateSupplyChain
{
    use AsAction;


    public function handle(Group $group): void
    {
        $stats  = [
            'number_stocks'         => Stock::count(),
            'number_stock_families' => StockFamily::count(),
        ];

        $stockFamilyStateCount = StockFamily::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach (StockFamilyStateEnum::cases() as $stockFamilyState) {
            $stats['number_stock_families_state_'.$stockFamilyState->snake()] = Arr::get($stockFamilyStateCount, $stockFamilyState->value, 0);
        }


        $stockStateCount = Stock::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach (OrgStockStateEnum::cases() as $stockState) {
            $stats['number_stocks_state_'.$stockState->snake()] = Arr::get($stockStateCount, $stockState->value, 0);
        }

        $stockQuantityStatusCount = Stock::selectRaw('quantity_status, count(*) as total')
            ->groupBy('quantity_status')
            ->pluck('total', 'quantity_status')->all();


        foreach (OrgStockQuantityStatusEnum::cases() as $stockQuantityStatus) {
            $stats['number_stocks_quantity_status_'.$stockQuantityStatus->snake()] = Arr::get($stockQuantityStatusCount, $stockQuantityStatus->value, 0);
        }


        $group->supplyChainStats()->update($stats);
    }


}
