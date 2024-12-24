<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 23-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateCustomerBalances
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
        $stats = [];

        $stats['number_customers_with_balances'] = DB::table('customers')
            ->where('shop_id', $shop->id)
            ->where('balance', '!=', 0)
            ->count();

        $stats['number_customers_with_positive_balances'] = DB::table('customers')
            ->where('shop_id', $shop->id)
            ->where('balance', '>', 0)
            ->count();

        $stats['number_customers_with_negative_balances'] = DB::table('customers')
            ->where('shop_id', $shop->id)
            ->where('balance', '<', 0)
            ->count();


        $shop->accountingStats()->update($stats);
    }
}
