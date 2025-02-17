<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 05-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateInvoices;
use App\Actions\Traits\WithEnumStats;
use App\Actions\Traits\WithIntervalsAggregators;
use App\Models\Helpers\Country;
use App\Models\SysAdmin\Group;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateCountryOrderingInterval
{
    use AsAction;
    use WithEnumStats;
    use WithHydrateInvoices;
    use WithIntervalsAggregators;


    private Group $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->group->id))->dontRelease()];
    }

    public function handle(Group $group): void
    {
        // calculate order country interval = (population + gdp) / order, the value we have to orders_all, etc
        $countries = Country::all();
        foreach ($countries as $country) {
            $group->countryOrderingIntervals()->create(['country_id' => $country->id]);
        }
    }


    public string $commandSignature = 'group:hydrate:country-ordering-interval';

    public function asCommand($command)
    {
        $group = Group::find(1);
        $this->handle($group);
    }


}
