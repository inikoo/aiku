<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 04:27:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant\Hydrators;

use App\Enums\Sales\Customer\CustomerStateEnum;
use App\Models\Central\Tenant;
use App\Models\Sales\Customer;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class TenantHydrateCustomers implements ShouldBeUnique
{
    use AsAction;

    public function handle(Tenant $tenant): void
    {
        $stats = [
            'number_customers' => Customer::count()
        ];


        $customerStatesCount = Customer::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach (CustomerStateEnum::values() as $customerState) {
            $stats['number_customers_state_'.str_replace('-', '_', $customerState)] = Arr::get($customerStatesCount, $customerState, 0);
        }

        $customerTradeStates      = ['none', 'one', 'many'];
        $customerTradeStatesCount = Customer::selectRaw('trade_state, count(*) as total')
            ->groupBy('trade_state')
            ->pluck('total', 'trade_state')->all();

        foreach ($customerTradeStates as $customerTradeState) {
            $stats['number_customers_trade_state_'.$customerTradeState] = Arr::get($customerTradeStatesCount, $customerTradeState, 0);
        }


        $tenant->salesStats->update($stats);
    }

    public function getJobUniqueId(Tenant $tenant): string
    {
        return $tenant->id;
    }

    public function getJobTags(): array
    {
        /** @var Tenant $tenant */
        $tenant=app('currentTenant');
        return ['central','tenant:'.$tenant->code];
    }
}
