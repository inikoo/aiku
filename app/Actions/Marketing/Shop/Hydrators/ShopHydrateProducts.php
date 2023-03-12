<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 16:06:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Shop\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Marketing\Product;
use App\Models\Marketing\Shop;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateProducts implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Shop $shop): void
    {
        $productStates = ['in-process', 'active', 'discontinuing', 'discontinued'];
        $stateCounts   = Product::where('shop_id', $shop->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        $stats         = [
            'number_products' => $shop->products->count(),
        ];
        foreach ($productStates as $productState) {
            $stats['number_products_state_'.str_replace('-', '_', $productState)] = Arr::get($stateCounts, $productState, 0);
        }
        $shop->stats->update($stats);
    }

    public function getJobUniqueId(Shop $shop): string
    {
        return $shop->id;
    }
}
