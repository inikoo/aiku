<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 20-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Models\SysAdmin\Group;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateUserRequests implements ShouldBeUnique
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

        $stats['number_user_requests'] = $group->users()->withCount('userRequests')->get()->sum('user_requests_count');

        $group->sysadminStats()->update($stats);
    }

    public string $commandSignature = 'hydrate:user-requests';

    public function asCommand($command): void
    {
        $group = Group::first();
        $this->handle($group);
    }
}
