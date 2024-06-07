<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:59:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStockFamily\Hydrators;

use App\Actions\Traits\WithIntervalsAggregators;
use App\Models\Dispatching\DeliveryNoteItem;
use App\Models\Inventory\OrgStockFamily;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgStockFamilyHydrateSales
{
    use AsAction;
    use WithIntervalsAggregators;


    private OrgStockFamily $orgStockFamily;

    public function __construct(OrgStockFamily $orgStockFamily)
    {
        $this->orgStockFamily = $orgStockFamily;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->orgStockFamily->id))->dontRelease()];
    }

    public function handle(OrgStockFamily $orgStockFamily): void
    {
        $stats = [];

        $queryBase = DeliveryNoteItem::where('org_stock_family_id', $orgStockFamily->id)->selectRaw('sum(group_net_amount) as  sum_group  , sum(group_net_amount) as  sum_org , sum(net) as  sum_shop  ');

        $stats=array_merge($stats, $this->processIntervalShopAssetsStats($queryBase));

        $orgStockFamily->stats()->update($stats);
    }


}
