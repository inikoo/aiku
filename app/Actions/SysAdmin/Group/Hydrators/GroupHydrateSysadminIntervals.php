<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 23-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Enums\DateIntervals\DateIntervalEnum;
use App\Enums\DateIntervals\PreviousQuartersEnum;
use App\Enums\DateIntervals\PreviousYearsEnum;
use App\Models\SysAdmin\Group;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateSysadminIntervals implements ShouldBeUnique
{
    use AsAction;

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

        $users = $group->users()->with('userRequests')->get();

        foreach (DateIntervalEnum::values() as $col) {
            $interval = DateIntervalEnum::from($col);
            $count = 0;

            foreach ($users as $user) {
                $userRequestsQuery = $user->userRequests;
                $count += $interval->wherePeriod($userRequestsQuery, 'date')->count();
            }
            $stats['user_requests_' . $col] = $count;
        }


        foreach (DateIntervalEnum::lastYearValues() as $col) {
            $interval = DateIntervalEnum::from($col);
            $count = 0;

            foreach ($users as $user) {
                $userRequestsQuery = $user->userRequests;
                $count += $interval->whereLastYearPeriod($userRequestsQuery, 'date')->count();
            }
            $stats['user_requests_' . $col . '_ly'] = $count;
        }

        foreach (PreviousYearsEnum::values() as $col) {
            $interval = PreviousYearsEnum::from($col);
            $count = 0;

            foreach ($users as $user) {
                $userRequestsQuery = $user->userRequests;
                $count += $interval->wherePeriod($userRequestsQuery, 'date')->count();
            }
            $stats['user_requests_' . $col] = $count;
        }

        foreach (PreviousQuartersEnum::values() as $col) {
            $interval = PreviousQuartersEnum::from($col);
            $count = 0;

            foreach ($users as $user) {
                $userRequestsQuery = $user->userRequests;
                $count += $interval->wherePeriod($userRequestsQuery, 'date')->count();
            }
            $stats['user_requests_' . $col] = $count;
        }

        $group->sysadminIntervals()->updateOrCreate([], $stats);
    }

    public string $commandSignature = 'hydrate:group_sysadmin_intervals';

    public function asCommand($command): void
    {
        $group = Group::first();
        $this->handle($group);
    }
}
