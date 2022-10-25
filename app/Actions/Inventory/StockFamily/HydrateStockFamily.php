<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 21:40:23 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\StockFamily;

use App\Actions\HydrateModel;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;


class HydrateStockFamily extends HydrateModel
{

    public string $commandSignature = 'hydrate:stock-family {tenant_code?} {id?} ';

    public function handle(StockFamily $stockFamily): void
    {
        $this->productsStats($stockFamily);
    }

    public function productsStats(StockFamily $stockFamily)
    {
        $stockStates = ['in-process', 'active', 'discontinuing', 'discontinued'];
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


    protected function getModel(int $id): StockFamily
    {
        return StockFamily::find($id);
    }

    protected function getAllModels(): Collection
    {
        return StockFamily::withTrashed()->get();
    }


}


