<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Apr 2024 17:05:15 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\Product\ProductTypeEnum;
use App\Models\Market\Product;
use App\Models\SysAdmin\Group;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateProducts
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
            'number_products' => $group->products()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'products',
                field: 'state',
                enum: ProductStateEnum::class,
                models: Product::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'products',
                field: 'type',
                enum: ProductTypeEnum::class,
                models: Product::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );


        $group->marketStats()->update($stats);
    }

}
