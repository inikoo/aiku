<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 May 2024 10:34:52 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\Production\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardAllowanceTypeEnum;
use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardTermsEnum;
use App\Models\Manufacturing\ManufactureTask;
use App\Models\Manufacturing\Production;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductionHydrateManufactureTasks
{
    use AsAction;
    use WithEnumStats;

    private Production $production;

    public function __construct(Production $production)
    {
        $this->production = $production;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->production->id))->dontRelease()];
    }


    public function handle(Production $production): void
    {
        $stats = [
            'number_manufacture_tasks' => $production->manufactureTasks()->count()
        ];



        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'manufacture_tasks',
                field: 'operative_reward_terms',
                enum: ManufactureTaskOperativeRewardTermsEnum::class,
                models: ManufactureTask::class,
                where: function ($q) use ($production) {
                    $q->where('production_id', $production->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'manufacture_tasks',
                field: 'operative_reward_allowance_type',
                enum: ManufactureTaskOperativeRewardAllowanceTypeEnum::class,
                models: ManufactureTask::class,
                where: function ($q) use ($production) {
                    $q->where('production_id', $production->id);
                }
            )
        );


        $production->stats()->update($stats);
    }
}
