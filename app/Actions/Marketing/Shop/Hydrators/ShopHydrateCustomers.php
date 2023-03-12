<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 04:18:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Shop\Hydrators;

use App\Actions\WithTenantJob;
use App\Enums\Sales\Customer\CustomerStateEnum;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateCustomers implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Shop $shop): void
    {
        $stats          = [
            'number_customers' => $shop->customers->count(),
        ];

        $stateCounts = Customer::where('shop_id', $shop->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach (CustomerStateEnum::values() as $customerState) {
            $stats['number_customers_state_'.str_replace('-', '_', $customerState)] =
                Arr::get($stateCounts, $customerState, 0);
        }

        $shop->stats->update($stats);
    }

    public function getJobUniqueId(Shop $shop): string
    {
        return $shop->id;
    }
}
