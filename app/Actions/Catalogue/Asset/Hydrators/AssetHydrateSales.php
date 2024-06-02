<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:59:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Asset\Hydrators;

use App\Actions\Traits\WithIntervalsAggregators;
use App\Models\Accounting\InvoiceTransaction;
use App\Models\Catalogue\Asset;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class AssetHydrateSales
{
    use AsAction;
    use WithIntervalsAggregators;


    private Asset $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->asset->id))->dontRelease()];
    }

    public function handle(Asset $asset): void
    {
        $stats = [];

        $queryBase = InvoiceTransaction::where('asset_id', $asset->id)->selectRaw('sum(group_net_amount) as  sum_group  , sum(group_net_amount) as  sum_org , sum(net) as  sum_shop  ');

        $stats=array_merge($stats, $this->processIntervalShopAssetsStats($queryBase));

        $asset->salesIntervals()->update($stats);
    }


}
