<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:14:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Enums\SupplyChain\Stock\StockStateEnum;
use App\Enums\SupplyChain\StockFamily\StockFamilyStateEnum;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use App\Models\SysAdmin\Group;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateSupplyChain
{
    use AsAction;

    private Group $group;
    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->group->id))->dontRelease()];
    }


    public function handle(Group $group): void
    {
        $stats  = [
            'number_stocks'         => $group->stocks()->count(),
            'number_stock_families' => $group->stockFamilies()->count(),
        ];

        $stockFamilyStateCount = StockFamily::selectRaw('state, count(*) as total')
            ->where('group_id', $group->id)
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach (StockFamilyStateEnum::cases() as $stockFamilyState) {
            $stats['number_stock_families_state_'.$stockFamilyState->snake()] = Arr::get($stockFamilyStateCount, $stockFamilyState->value, 0);
        }


        $stockStateCount = Stock::selectRaw('state, count(*) as total')
            ->where('group_id', $group->id)
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach (StockStateEnum::cases() as $stockState) {
            $stats['number_stocks_state_'.$stockState->snake()] = Arr::get($stockStateCount, $stockState->value, 0);
        }




        $group->supplyChainStats()->update($stats);
    }


}
