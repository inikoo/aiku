<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:59:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\Hydrators;

use App\Actions\Traits\WithIntervalsAggregators;
use App\Models\Dispatch\DeliveryNoteItem;
use App\Models\Inventory\OrgStock;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgStockHydrateSales
{
    use AsAction;
    use WithIntervalsAggregators;


    private OrgStock $orgStock;

    public function __construct(OrgStock $orgStock)
    {
        $this->orgStock = $orgStock;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->orgStock->id))->dontRelease()];
    }

    public function handle(OrgStock $orgStock): void
    {
        $stats = [];

        $queryBase = DeliveryNoteItem::where('org_stock_id', $orgStock->id)->selectRaw('sum(group_net_amount) as  sum_group  , sum(group_net_amount) as  sum_org , sum(net) as  sum_shop  ');

        $stats=array_merge($stats, $this->processIntervalShopAssetsStats($queryBase));

        $orgStock->stats()->update($stats);
    }


}
