<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 04:27:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant\Hydrators;

use App\Enums\Sales\Customer\CustomerStateEnum;
use App\Enums\Sales\Customer\CustomerTradeStateEnum;
use App\Models\Central\Tenant;
use App\Models\Sales\Customer;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class TenantHydrateCustomers implements ShouldBeUnique
{
    use AsAction;
    use HasTenantHydrate;

    public function handle(Tenant $tenant): void
    {
        $stats = [
            'number_customers' => Customer::count()
        ];


        $customerStatesCount = Customer::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach (CustomerStateEnum::cases() as $customerState) {
            $stats['number_customers_state_'.$customerState->snake()] = Arr::get($customerStatesCount, $customerState->value, 0);
        }

        $customerTradeStatesCount = Customer::selectRaw('trade_state, count(*) as total')
            ->groupBy('trade_state')
            ->pluck('total', 'trade_state')->all();

        foreach (CustomerTradeStateEnum::cases() as $customerTradeState) {
            $stats['number_customers_trade_state_'.$customerTradeState->snake()] = Arr::get($customerTradeStatesCount, $customerTradeState->value, 0);
        }


        $tenant->salesStats->update($stats);
    }
}
