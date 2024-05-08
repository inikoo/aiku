<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 07 May 2024 22:41:39 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStateEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStockStatusEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialUnitEnum;
use App\Models\Manufacturing\RawMaterial;
use App\Models\SysAdmin\Group;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateRawMaterials
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
            'number_raw_materials' => $group->rawMaterials()->count()
        ];



        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'raw_materials',
                field: 'type',
                enum: RawMaterialTypeEnum::class,
                models: RawMaterial::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'raw_materials',
                field: 'state',
                enum: RawMaterialStateEnum::class,
                models: RawMaterial::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'raw_materials',
                field: 'unit',
                enum: RawMaterialUnitEnum::class,
                models: RawMaterial::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'raw_materials',
                field: 'stock_status',
                enum: RawMaterialStockStatusEnum::class,
                models: RawMaterial::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $group->manufactureStats()->update($stats);
    }
}
