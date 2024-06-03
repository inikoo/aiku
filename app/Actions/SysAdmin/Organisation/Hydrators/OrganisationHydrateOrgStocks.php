<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Mar 2024 18:53:55 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use App\Models\Inventory\OrgStock;
use App\Models\SysAdmin\Organisation;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateOrgStocks
{
    use AsAction;
    use WithEnumStats;

    private Organisation $organisation;

    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->organisation->id))->dontRelease()];
    }


    public function handle(Organisation $organisation): void
    {

        $stats = [
            'number_org_stocks'                  => $organisation->orgStocks()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'org_stocks',
                field: 'state',
                enum: OrgStockStateEnum::class,
                models: OrgStock::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );

        $stats['number_current_org_stocks'] =
            Arr::get($stats, 'number_org_stocks_state_active', 0) +
            Arr::get($stats, 'number_org_stocks_state_discontinuing', 0);

        $organisation->inventoryStats()->update($stats);
    }
}
