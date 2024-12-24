<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:59:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithIntervalsAggregators;
use App\Models\Accounting\InvoiceTransaction;
use App\Models\Catalogue\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateSales
{
    use AsAction;
    use WithIntervalsAggregators;

    public string $jobQueue = 'sales';


    private Shop $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->shop->id))->dontRelease()];
    }

    public function handle(Shop $shop): void
    {
        $stats = [];
        $queryBase = InvoiceTransaction::where('shop_id', $shop->id)->selectRaw('sum(net_amount) as  sum_aggregate  ');
        $stats = $this->getIntervalsData($stats, $queryBase, 'sales_');

        $queryBase = InvoiceTransaction::where('shop_id', $shop->id)->selectRaw('sum(grp_net_amount) as  sum_aggregate');
        $stats = $this->getIntervalsData($stats, $queryBase, 'sales_grp_currency_');

        $queryBase = InvoiceTransaction::where('shop_id', $shop->id)->selectRaw('sum(org_net_amount) as  sum_aggregate');
        $stats = $this->getIntervalsData($stats, $queryBase, 'sales_org_currency_');

        $shop->salesIntervals()->update($stats);
    }


}
