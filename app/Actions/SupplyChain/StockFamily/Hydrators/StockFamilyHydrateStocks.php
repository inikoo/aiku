<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 13:06:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\StockFamily\Hydrators;

use App\Enums\Inventory\Stock\StockQuantityStatusEnum;
use App\Enums\Inventory\Stock\StockStateEnum;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StockFamilyHydrateStocks implements ShouldBeUnique
{
    use AsAction;


    public function handle(StockFamily $stockFamily): void
    {
        $stats = [
            'number_stocks' => $stockFamily->stocks()->count(),
        ];

        $stateCounts = Stock::where('stock_family_id', $stockFamily->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (StockStateEnum::cases() as $stockState) {
            $stats['number_stocks_state_'.$stockState->snake()] = Arr::get($stateCounts, $stockState->value, 0);
        }

        $quantityStatusCounts = Stock::where('stock_family_id', $stockFamily->id)
            ->selectRaw('quantity_status, count(*) as total')
            ->groupBy('quantity_status')
            ->pluck('total', 'quantity_status')->all();

        foreach (StockQuantityStatusEnum::cases() as $quantityStatus) {
            $stats['number_stocks_quantity_status_'.$quantityStatus->snake()] = Arr::get($quantityStatusCounts, $quantityStatus->value, 0);
        }


        $stockFamily->stats()->update($stats);
    }

    public function getJobUniqueId(StockFamily $stockFamily): int
    {
        return $stockFamily->id;
    }
}
