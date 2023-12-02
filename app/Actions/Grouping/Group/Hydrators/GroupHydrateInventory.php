<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 29 Nov 2023 21:44:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Grouping\Group\Hydrators;

use App\Enums\Inventory\Stock\StockQuantityStatusEnum;
use App\Enums\Inventory\Stock\StockStateEnum;
use App\Enums\Inventory\StockFamily\StockFamilyStateEnum;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;
use App\Models\Grouping\Group;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateInventory implements ShouldBeUnique
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


        foreach (StockStateEnum::cases() as $stockState) {
            $stats['number_stocks_state_'.$stockState->snake()] = Arr::get($stockStateCount, $stockState->value, 0);
        }

        $stockQuantityStatusCount = Stock::selectRaw('quantity_status, count(*) as total')
            ->groupBy('quantity_status')
            ->pluck('total', 'quantity_status')->all();


        foreach (StockQuantityStatusEnum::cases() as $stockQuantityStatus) {
            $stats['number_stocks_quantity_status_'.$stockQuantityStatus->snake()] = Arr::get($stockQuantityStatusCount, $stockQuantityStatus->value, 0);
        }


        $group->inventoryStats->update($stats);
    }

    public function getJobUniqueId(Group $group): string
    {
        return $group->id;
    }
}
