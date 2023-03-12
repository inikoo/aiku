<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 16:32:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\StockFamily\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StockFamilyHydrateStocks implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(StockFamily $stockFamily): void
    {
        $stockStates   = ['in-process', 'active', 'discontinuing', 'discontinued'];
        $stateCounts   = Stock::where('stock_family_id', $stockFamily->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        $stats         = [
            'number_stocks' => $stockFamily->stocks->count(),
        ];
        foreach ($stockStates as $stockState) {
            $stats['number_stocks_state_'.str_replace('-', '_', $stockState)] = Arr::get($stateCounts, $stockState, 0);
        }
        $stockFamily->stats->update($stats);
    }

    public function getJobUniqueId(StockFamily $stockFamily): int
    {
        return $stockFamily->id;
    }
}
