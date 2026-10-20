<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 14 Apr 2025 18:34:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ShopPlatformStats;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Shop;
use App\Models\Dropshipping\Platform;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopPlatformStatsHydratePortfolios implements ShouldBeUnique
{
    use AsAction;
    use WithEnumStats;

    public string $jobQueue = 'hydrators-slave';

    public function getJobUniqueId(Shop $shop, Platform $platform): string
    {
        return $shop->id.'-'.$platform->id;
    }

    public function handle(Shop $shop, Platform $platform): void
    {
        $selects  = ['count(distinct portfolios.item_id) as number_products'];
        $bindings = [];
        foreach (ProductStateEnum::cases() as $state) {
            $selects[]  = 'count(distinct portfolios.item_id) filter (where products.state = ?) as number_products_state_'.$state->snake();
            $bindings[] = $state->value;
        }

        $stats = (array) DB::table('portfolios')
            ->leftJoin('products', 'portfolios.item_id', '=', 'products.id')
            ->where('portfolios.item_type', class_basename(Product::class))
            ->where('portfolios.shop_id', $shop->id)
            ->where('portfolios.platform_id', $platform->id)
            ->selectRaw(implode(', ', $selects), $bindings)
            ->first();

        $shop->platformStats()->where('platform_id', $platform->id)->update($stats);
    }
}
