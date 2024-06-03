<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 03 Jun 2024 17:07:43 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\SupplyChain\StockFamily\StockFamilyStateEnum;
use App\Models\SupplyChain\StockFamily;
use App\Models\SysAdmin\Group;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateStockFamilies
{
    use AsAction;
    use WithEnumStats;


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
            'number_stock_families' => $group->stockFamilies()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'stock_families',
                field: 'state',
                enum: StockFamilyStateEnum::class,
                models: StockFamily::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $stats['number_current_stock_families']=
            Arr::get($stats, 'number_stock_families_state_active', 0)+
            Arr::get($stats, 'number_stock_families_state_discontinuing', 0);

        $group->inventoryStats()->update($stats);

    }


}
