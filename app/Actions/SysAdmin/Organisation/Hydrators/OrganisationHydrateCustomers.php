<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Enums\CRM\Customer\CustomerTradeStateEnum;
use App\Models\CRM\Customer;
use App\Models\SysAdmin\Organisation;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateCustomers implements ShouldBeUnique
{
    use AsAction;


    public function handle(Organisation $organisation): void
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


        $organisation->crmStats()->update($stats);
    }
}
