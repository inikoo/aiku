<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 21-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateTopSallers
{
    use AsAction;
    use WithEnumStats;

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
        $timesUpdate = ['1d', '1w', '1m', '1y', 'all'];
        foreach ($timesUpdate as $timeUpdate) {
            $topFamily = $shop->families()->sortByDesc(function ($family) use ($timeUpdate) {
                return $family->stats->{'shop_amount_' . $timeUpdate};
            })->first();

            $topDepartment = $shop->departments()->sortByDesc(function ($department) use ($timeUpdate) {
                return $department->stats->{'shop_amount_' . $timeUpdate};
            })->first();

            $topProduct = $shop->products()
                ->join('product_sales_intervals', 'products.id', '=', 'product_sales_intervals.product_id')
                ->orderByDesc('product_sales_intervals.shop_amount_' . $timeUpdate)
                ->first();


            $shop->stats->update([
                "top_{$timeUpdate}_family_id" => $topFamily->id,
                "top_{$timeUpdate}_department_id" => $topDepartment->id,
                "top_{$timeUpdate}_product_id" => $topProduct->id,
            ]);
        }
    }

    public string $commandSignature = 'hydrate:top-sallers';

    public function asCommand($command)
    {
        $shops = SHOP::all();
        foreach ($shops as $shop) {
            $this->handle($shop);
        }
    }


}
