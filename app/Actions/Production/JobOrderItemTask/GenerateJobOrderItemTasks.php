<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 08 Aug 2026 21:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\JobOrderItemTask;

use App\Actions\Production\ManufactureTask\GetDefaultManufactureTask;
use App\Enums\Production\JobOrderItemTask\JobOrderItemTaskStateEnum;
use App\Models\Production\JobOrderItem;
use App\Models\Production\JobOrderItemTask;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateJobOrderItemTasks
{
    use AsAction;

    /**
     * A step that takes the place of a removed one is not asked for again where the removed step is already
     * finished, so the same work neither holds up the steps after it nor is paid twice.
     *
     * @param  array<int, int>  $replacedSteps  removed manufacture task id by the id of the step replacing it
     * @return array<int, JobOrderItemTask>
     */
    public function handle(JobOrderItem $jobOrderItem, array $replacedSteps = []): array
    {
        $jobOrder = $jobOrderItem->jobOrder;
        $tasks    = [];
        $artefact = $jobOrderItem->artefact;

        if ($artefact->manufactureTasks()->doesntExist()) {
            $artefact->manufactureTasks()->attach(GetDefaultManufactureTask::run($jobOrder->production)->id, ['position' => 1, 'units_per_artefact' => 1]);
        }

        $finishedTaskIds = $jobOrderItem->tasks()->where('state', JobOrderItemTaskStateEnum::DONE)->pluck('manufacture_task_id');

        foreach ($artefact->manufactureTasks()->get() as $manufactureTask) {
            if ($finishedTaskIds->contains($replacedSteps[$manufactureTask->id] ?? null)
                && $jobOrderItem->tasks()->where('manufacture_task_id', $manufactureTask->id)->doesntExist()) {
                continue;
            }

            $task = JobOrderItemTask::firstOrCreate(
                [
                    'job_order_item_id'   => $jobOrderItem->id,
                    'manufacture_task_id' => $manufactureTask->id,
                ],
                [
                    'group_id'          => $jobOrderItem->group_id,
                    'organisation_id'   => $jobOrderItem->organisation_id,
                    'production_id'     => $jobOrder->production_id,
                    'job_order_id'      => $jobOrder->id,
                    'state'             => JobOrderItemTaskStateEnum::TODO,
                    'position'          => $manufactureTask->pivot->position,
                    'quantity_required' => $jobOrderItem->quantity * $manufactureTask->pivot->units_per_artefact,
                ]
            );

            if ($task->state == JobOrderItemTaskStateEnum::TODO && $task->position != $manufactureTask->pivot->position) {
                $task->update(['position' => $manufactureTask->pivot->position]);
            }

            $tasks[] = $task;
        }

        return $tasks;
    }
}
