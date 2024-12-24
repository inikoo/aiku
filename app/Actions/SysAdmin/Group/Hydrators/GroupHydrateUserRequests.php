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
use Illuminate\Support\Facades\DB;
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
        $userIds = DB::table('users')->where('group_id', $group->id)->selectRaw('id')->pluck('id')->toArray(); // do like this cause the user_requests table doesn't have group_id
        $stats['number_user_requests'] = DB::table('user_requests')->whereIn('user_id', $userIds)->count();
        $group->sysadminStats()->update($stats);
    }


}
