<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\StockFamily\Hydrators;

use App\Actions\Goods\StockFamily\UpdateStockFamily;
use App\Actions\Traits\WithEnumStats;
use App\Enums\SupplyChain\Stock\StockStateEnum;
use App\Enums\SupplyChain\StockFamily\StockFamilyStateEnum;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StockFamilyHydrateStocks
{
    use AsAction;
    use WithEnumStats;


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

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'stocks',
                field: 'state',
                enum: StockStateEnum::class,
                models: Stock::class,
                where: function ($q) use ($stockFamily) {
                    $q->where('stock_family_id', $stockFamily->id);
                }
            )
        );


        UpdateStockFamily::make()->action(
            $stockFamily,
            [
                'state' => $this->getStockFamilyState($stats)
            ]
        );


        $stockFamily->stats()->update($stats);
    }

    public function getStockFamilyState($stats): StockFamilyStateEnum
    {
        if($stats['number_stocks'] == 0) {
            return StockFamilyStateEnum::IN_PROCESS;
        }

        if(Arr::get($stats, 'number_stocks_state_active', 0)>0) {
            return StockFamilyStateEnum::ACTIVE;
        }

        if(Arr::get($stats, 'number_stocks_state_discontinuing', 0)>0) {
            return StockFamilyStateEnum::DISCONTINUING;
        }

        if(Arr::get($stats, 'number_stocks_state_in_process', 0)>0) {
            return StockFamilyStateEnum::IN_PROCESS;
        }

        return StockFamilyStateEnum::DISCONTINUED;

    }


}
