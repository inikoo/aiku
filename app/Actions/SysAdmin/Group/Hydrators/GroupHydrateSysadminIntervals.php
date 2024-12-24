<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 23-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Actions\Traits\WithIntervalsAggregators;
use App\Models\SysAdmin\Group;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateSysadminIntervals implements ShouldBeUnique
{
    use AsAction;
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

        $stats = [];
        $customerIds = DB::table('users')->where('group_id', $group->id)->pluck('id')->toArray(); // do like this cause the user_requests table doesn't have group_id
        $queryBase = DB::table('user_requests')->whereIn('user_id', $customerIds)->selectRaw('count(*) as  sum_aggregate ');
        $stats = array_merge(
            $stats,
            $this->getIntervalsData($stats, $queryBase, 'user_requests_'),
            $this->getPreviousYearsIntervalStats($queryBase, 'user_requests_'),
            $this->getPreviousQuartersIntervalStats($queryBase, 'user_requests_')
        );
        $group->sysadminIntervals->update($stats);
    }


}
