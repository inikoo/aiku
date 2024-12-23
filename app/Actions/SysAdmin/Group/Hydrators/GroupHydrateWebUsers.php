<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 20-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\CRM\WebUser\WebUserAuthTypeEnum;
use App\Enums\CRM\WebUser\WebUserTypeEnum;
use App\Models\CRM\WebUser;
use App\Models\SysAdmin\Group;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateWebUsers
{
    use AsAction;
    use WithEnumStats;

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
        $stats = [
            'number_web_users' => $group->WebUsers()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'web_users',
                field: 'auth_type',
                enum: WebUserAuthTypeEnum::class,
                models: WebUser::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'web_users',
                field: 'type',
                enum: WebUserTypeEnum::class,
                models: WebUser::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $group->crmStats()->updateOrCreate([], $stats);
    }
    public string $commandSignature = 'hydrate:group_web_users';

    public function asCommand($command): void
    {
        $group = Group::first();
        $this->handle($group);
    }


}
