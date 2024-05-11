<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 04:23:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Enums\CRM\Customer\CustomerTradeStateEnum;
use App\Models\CRM\Customer;
use App\Models\Catalogue\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateCustomerInvoices
{
    use AsAction;

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

        $numberInvoicesStateCounts = Customer::where('shop_id', $shop->id)
            ->selectRaw('trade_state, count(*) as total')
            ->groupBy('trade_state')
            ->pluck('total', 'trade_state')->all();


        foreach (CustomerTradeStateEnum::cases() as $tradeState) {
            $stats['number_customers_trade_state_'.$tradeState->snake()] =
                Arr::get($numberInvoicesStateCounts, $tradeState->value, 0);
        }
        $shop->crmStats()->update($stats);
    }


}
