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

        $queryBase = DeliveryNoteItem::where('org_stock_family_id', $orgStockFamily->id)->selectRaw('sum(org_revenue_amount) as  sum_aggregate  ');
        $stats = $this->getIntervalsData($stats, $queryBase, 'revenue_org_currency_');

        $queryBase = DeliveryNoteItem::where('org_stock_family_id', $orgStockFamily->id)->selectRaw('sum(grp_revenue_amount) as  sum_aggregate  ');
        $stats = $this->getIntervalsData($stats, $queryBase, 'revenue_grp_currency_');

        $queryBase = DeliveryNoteItem::where('org_stock_family_id', $orgStockFamily->id)->selectRaw('sum(quantity_dispatched) as  sum_aggregate  ');
        $stats = $this->getIntervalsData($stats, $queryBase, 'dispatched_');

        $orgStockFamily->intervals()->update($stats);
    }


}
