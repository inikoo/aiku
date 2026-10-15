<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-18h-25m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageStateEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentStage;
use Lorisleiva\Actions\Concerns\AsAction;

class GetProductDevelopmentStages
{
    use AsAction;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(ProductDevelopment $productDevelopment): array
    {
        $stageLabels = ProductDevelopmentStageEnum::labels();
        $roleLabels  = ProductDevelopmentMemberRoleEnum::labels();
        $stateLabels = ProductDevelopmentStageStateEnum::labels();

        return $productDevelopment->stages
            ->groupBy('position')
            ->map(function ($stages, int $position) use ($productDevelopment, $stageLabels, $roleLabels, $stateLabels) {
                return [
                    'position' => $position,
                    'label'    => ProductDevelopmentStageEnum::positionLabel($position),
                    'current'  => $position === $productDevelopment->current_position,
                    'done'     => $stages->every(fn (ProductDevelopmentStage $stage) => $stage->state->isClosed()),
                    'stages'   => $stages->map(fn (ProductDevelopmentStage $stage) => [
                        'key'          => $stage->stage->value,
                        'number'       => $stage->position.($stage->sub_position ?? ''),
                        'label'        => $stageLabels[$stage->stage->value],
                        'role'         => $roleLabels[$stage->role->value],
                        'state'        => $stage->state->value,
                        'state_label'  => $stateLabels[$stage->state->value],
                        'due_at'       => $stage->due_at?->toDateString(),
                        'is_late'      => $stage->state === ProductDevelopmentStageStateEnum::IN_PROGRESS && $stage->due_at?->isPast() && !$stage->due_at->isToday(),
                        'started_at'   => $stage->started_at?->toIso8601String(),
                        'completed_at' => $stage->completed_at?->toIso8601String(),
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();
    }
}
