<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:59:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory\Hydrators;

use App\Actions\Traits\WithIntervalsAggregators;
use App\Models\Accounting\InvoiceTransaction;
use App\Models\Catalogue\ProductCategory;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductCategoryHydrateSales
{
    use AsAction;
    use WithIntervalsAggregators;


    private ProductCategory $productCategory;

    public function __construct(ProductCategory $productCategory)
    {
        $this->productCategory = $productCategory;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->productCategory->id))->dontRelease()];
    }

    public function handle(ProductCategory $productCategory): void
    {
        $stats = [];

        $queryBase = InvoiceTransaction::where('shop_id', $productCategory->shop->id)->selectRaw('sum(group_net_amount) as  sum_group  , sum(group_net_amount) as  sum_org , sum(net) as  sum_shop  ');

        $stats=array_merge($stats, $this->processIntervalShopAssetsStats($queryBase));

        $productCategory->salesIntervals()->update($stats);
    }


}
