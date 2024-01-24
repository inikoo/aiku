<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 13:06:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\StockFamily\Hydrators;

use App\Enums\SupplyChain\Stock\StockStateEnum;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StockFamilyHydrateStocks
{
    use AsAction;

    private StockFamily $stockFamily;
    public function __construct(StockFamily $stockFamily)
    {
        $this->stockFamily = $stockFamily;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->stockFamily->id))->dontRelease()];
    }


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



        $stockFamily->stats()->update($stats);
    }


}
