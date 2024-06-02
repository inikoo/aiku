<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 10:22:42 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Catalogue\Subscription\SubscriptionStateEnum;
use App\Models\Catalogue\Subscription;
use App\Models\SysAdmin\Group;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateSubscriptions
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

        $stats         = [
            'number_subscriptions' => $group->subscriptions()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'subscriptions',
                field: 'state',
                enum: SubscriptionStateEnum::class,
                models: Subscription::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $group->marketStats()->update($stats);


    }

}
