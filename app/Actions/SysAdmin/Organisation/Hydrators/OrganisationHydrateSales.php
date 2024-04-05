<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Apr 2024 11:19:04 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Actions\Traits\WithIntervalsAggregators;
use App\Models\Accounting\Invoice;
use App\Models\SysAdmin\Organisation;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateSales
{
    use AsAction;
    use WithIntervalsAggregators;


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

        $queryBase = Invoice::where('organisation_id', $organisation->id)->selectRaw('sum(group_net_amount) as  sum_group  , sum(group_net_amount) as  sum_org  ');

        $stats=array_merge($stats, $this->getIntervalStats($queryBase, 'group_amount_', 'date', 'sum_group'));
        $stats=array_merge($stats, $this->getLastYearIntervalStats($queryBase, 'group_amount_', 'date', 'sum_group'));

        $stats=array_merge($stats, $this->getIntervalStats($queryBase, 'org_amount_', 'date', 'sum_org'));
        $stats=array_merge($stats, $this->getLastYearIntervalStats($queryBase, 'org_amount_', 'date', 'sum_org'));


        $organisation->salesStats()->update($stats);
    }


}
