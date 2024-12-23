<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:59:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\Hydrators;

use App\Actions\Traits\WithIntervalsAggregators;
use App\Models\Dispatching\DeliveryNoteItem;
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

        $queryBase = DeliveryNoteItem::where('org_stock_id', $orgStock->id)->selectRaw('sum(org_revenue_amount) as  sum_aggregate  ');
        $stats = $this->getIntervalsData($stats, $queryBase, 'org_amount_revenue_');

        $queryBase = DeliveryNoteItem::where('org_stock_id', $orgStock->id)->selectRaw('sum(grp_revenue_amount) as  sum_aggregate  ');
        $stats = $this->getIntervalsData($stats, $queryBase, 'group_amount_revenue_');

        $queryBase = DeliveryNoteItem::where('org_stock_id', $orgStock->id)->selectRaw('sum(quantity_dispatched) as  sum_aggregate  ');
        $stats = $this->getIntervalsData($stats, $queryBase, 'dispatched_');


        $orgStock->intervals()->update($stats);
    }


}
