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
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Accounting\Invoice;
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
        $countries = Country::all();
        foreach ($countries as $country) {
            $group->countryOrderingIntervals()->create(['country_id' => $country->id]);
        }
        // $stats = [];
        // $queryBase = Invoice::where('group_id', $group->id)->where('type', InvoiceTypeEnum::INVOICE)->selectRaw('count(*) as  sum_aggregate');
        // $stats     = $this->getIntervalsData(
        //     stats: $stats,
        //     queryBase: $queryBase,
        //     statField: 'invoices_'
        // );

        // $stats = $this->getInvoicesStats($group);

        // $stats = array_merge(
        //     $stats,
        //     $this->getEnumStats(
        //         model: 'invoices',
        //         field: 'type',
        //         enum: InvoiceTypeEnum::class,
        //         models: Invoice::class,
        //         where: function ($q) use ($group) {
        //             $q->where('group_id', $group->id);
        //         }
        //     )
        // );

        // dd($group->countryOrderingIntervals);


        // $group->orderingStats()->update($stats);
    }


    public string $commandSignature = 'group:hydrate:country-ordering-interval';

    public function asCommand($command)
    {
        $group = Group::find(1);
        $this->handle($group);
    }


}
