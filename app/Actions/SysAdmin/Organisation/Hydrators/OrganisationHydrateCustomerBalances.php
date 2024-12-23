<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 23-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\SysAdmin\Organisation;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateCustomerBalances
{
    use AsAction;
    use WithEnumStats;
    private Organisation $organisation;

    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->organisation->id))->dontRelease()];
    }

    public function handle(Organisation $organisation): void
    {
        $stats = [];

        $stats['number_customers_with_balances'] = $organisation->customers->filter(function ($customer) {
            return $customer->balance !== null;
        })->count();

        $stats['number_customers_with_positive_balances']  = $organisation->customers->filter(function ($customer) {
            return $customer->balance > 0;
        })->count();

        $stats['number_customers_with_negative_balances']  = $organisation->customers->filter(function ($customer) {
            return $customer->balance < 0;
        })->count();


        $organisation->accountingStats()->update($stats);
    }
}
