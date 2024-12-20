<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Apr 2024 17:05:15 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Models\Catalogue\Asset;
use App\Models\SysAdmin\Group;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateAssets
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
            'number_assets' => $group->assets()->where('is_main', true)->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'assets',
                field: 'state',
                enum: AssetStateEnum::class,
                models: Asset::class,
                where: function ($q) use ($group) {
                    $q->where('is_main', true)->where('group_id', $group->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'assets',
                field: 'type',
                enum: AssetTypeEnum::class,
                models: Asset::class,
                where: function ($q) use ($group) {
                    $q->where('is_main', true)->where('group_id', $group->id);
                }
            )
        );


        $group->catalogueStats()->update($stats);
    }

}
