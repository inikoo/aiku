<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 08 Aug 2026 21:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\JobOrderItemTask;

use App\Actions\Production\ManufactureTask\GetDefaultManufactureTask;
use App\Enums\Production\JobOrderItemTask\JobOrderItemTaskStateEnum;
use App\Enums\Production\JobOrder\JobOrderStateEnum;
use App\Models\Production\Artefact;
use App\Models\Production\ArtefactManufactureTask;
use App\Models\Production\JobOrderItem;
use App\Models\Production\JobOrderItemTask;
use App\Models\Production\ManufactureTask;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateJobOrderItemTasks
{
    use AsAction;

    /**
     * A step that takes the place of a removed one is not asked for again where the removed step is already
     * finished, so the same work neither holds up the steps after it nor is paid twice.
     *
     * A step new to the recipe is not added where a step after it already has work, since that work was done without it.
     *
     * A step with nothing recorded yet, even one already started, follows a change of the recipe's units per
     * artefact; once work is recorded the step keeps what it asked for, so nothing already counted or paid is restated.
     *
     * @param  array<int, int>  $replacedSteps  removed manufacture task id by the id of the step replacing it
     * @param  array<int, float|string>  $previousUnitsPerArtefact  units per artefact before the recipe changed, by manufacture task id
     * @return array<int, JobOrderItemTask>
     */
    public function handle(JobOrderItem $jobOrderItem, array $replacedSteps = [], array $previousUnitsPerArtefact = []): array
    {
        $jobOrder = $jobOrderItem->jobOrder;
        $tasks    = [];
        $artefact = $jobOrderItem->artefact;

        if ($artefact->manufactureTasks()->doesntExist()) {
            $artefact->manufactureTasks()->attach(GetDefaultManufactureTask::run($jobOrder->production)->id, ['position' => 1, 'units_per_artefact' => 1]);
        }

        $finishedTaskIds = $jobOrderItem->tasks()->where('state', JobOrderItemTaskStateEnum::DONE)->pluck('manufacture_task_id');
        $existingTaskIds = $jobOrderItem->tasks()->pluck('manufacture_task_id');
        $workedTaskIds   = $jobOrderItem->tasks()->whereNot(fn ($query) => $query->unworked())->pluck('manufacture_task_id');
        $recipeSteps     = $artefact->manufactureTasks()->get();

        foreach ($recipeSteps as $manufactureTask) {
            $isNewStep         = !$existingTaskIds->contains($manufactureTask->id);
            $laterStepIsWorked = $recipeSteps
                ->filter(fn (ManufactureTask $step) => $step->pivot->position > $manufactureTask->pivot->position)
                ->pluck('id')
                ->intersect($workedTaskIds)
                ->isNotEmpty();

            if ($isNewStep && ($laterStepIsWorked || $finishedTaskIds->contains($replacedSteps[$manufactureTask->id] ?? null))) {
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
                    'quantity_required' => ArtefactManufactureTask::stepQuantity($jobOrderItem->quantity, $manufactureTask->pivot->units_per_artefact),
                ]
            );

            if ($task->state == JobOrderItemTaskStateEnum::TODO && $task->position != $manufactureTask->pivot->position) {
                $task->update(['position' => $manufactureTask->pivot->position]);
            }

            $previousUnits = ArtefactManufactureTask::exactUnits($previousUnitsPerArtefact[$manufactureTask->id] ?? 0);
            $units         = ArtefactManufactureTask::exactUnits($manufactureTask->pivot->units_per_artefact);
            if (!$task->wasRecentlyCreated && $previousUnits > 0 && abs($previousUnits - $units) > 1e-9
                && $task->state != JobOrderItemTaskStateEnum::DONE && (float) $task->quantity_made == 0) {
                $task->setRequiredQuantity(round((float) $task->quantity_required * $units / $previousUnits, 3));
            }

            $tasks[] = $task;
        }

        if (in_array($jobOrder->state, JobOrderStateEnum::open())) {
            $jobOrderItem->tasks()
                ->whereNotIn('manufacture_task_id', $artefact->manufactureTasks()->pluck('manufacture_tasks.id'))
                ->unworked()
                ->delete();
            $jobOrderItem->unsetRelation('tasks');
        }

        return $tasks;
    }

    /**
     * What a recipe change is about to do to the open job orders of an artefact, so whoever saves it is told and confirms first.
     *
     * @param  array<int, float|string>  $newUnitsPerArtefact  units per artefact the recipe is changing to, by manufacture task id; steps left out are unchanged
     * @param  array<int, int>  $removedTaskIds  steps the change takes out of the recipe
     * @return array{open: Collection<int, string>, keeping: Collection<int, string>}|null null when no open job order is affected
     */
    public function openJobsAffectedBy(Artefact $artefact, array $newUnitsPerArtefact, array $removedTaskIds = []): ?array
    {
        $currentUnits   = $artefact->manufactureTasks()->get()->mapWithKeys(fn (ManufactureTask $manufactureTask) => [$manufactureTask->id => $manufactureTask->pivot->units_per_artefact]);
        $changedTaskIds = collect($newUnitsPerArtefact)
            ->filter(fn ($units, int $taskId) => !$currentUnits->has($taskId)
                || abs(ArtefactManufactureTask::exactUnits($currentUnits[$taskId]) - ArtefactManufactureTask::exactUnits($units)) > 1e-9)
            ->keys()
            ->merge($removedTaskIds);

        if ($changedTaskIds->isEmpty()) {
            return null;
        }

        $openItems = JobOrderItem::where('artefact_id', $artefact->id)
            ->whereHas('jobOrder', fn ($query) => $query->whereNotIn('state', [JobOrderStateEnum::RECEIVED, JobOrderStateEnum::NOT_RECEIVED]))
            ->with('jobOrder')
            ->get();

        if ($openItems->isEmpty()) {
            return null;
        }

        $label = fn (JobOrderItem $jobOrderItem) => $jobOrderItem->jobOrder->reference.' '.$artefact->code;

        return [
            'open'    => $openItems->map($label)->unique()->values(),
            'keeping' => JobOrderItemTask::whereIn('job_order_item_id', $openItems->pluck('id'))
                ->whereIn('manufacture_task_id', $changedTaskIds)
                ->where('quantity_made', '>', 0)
                ->get()
                ->map(fn (JobOrderItemTask $task) => $label($openItems->firstWhere('id', $task->job_order_item_id)))
                ->unique()
                ->values(),
        ];
    }

    /**
     * @param  array<int, array{open: Collection<int, string>, keeping: Collection<int, string>}|null>  $affected
     */
    public static function openJobsConfirmationMessage(array $affected): ?string
    {
        $affected = array_filter($affected);
        if (!$affected) {
            return null;
        }

        $open    = collect($affected)->flatMap(fn (array $jobs) => $jobs['open'])->unique();
        $keeping = collect($affected)->flatMap(fn (array $jobs) => $jobs['keeping'])->unique();

        $message = __('Open job orders that change with this recipe: :jobs.', ['jobs' => $open->implode(', ')]);
        if ($keeping->isNotEmpty()) {
            $message .= ' '.__('Work is already recorded on :jobs, so those steps keep their old count.', ['jobs' => $keeping->implode(', ')]);
        }

        return $message;
    }

    /**
     * @param  array<int, float|string>  $previousUnitsPerArtefact  units per artefact before the recipe changed, by manufacture task id
     * @return Collection<int, string> open job orders with work recorded on a step whose units changed, which keep the count they had
     */
    public function jobsKeepingTheirCount(Artefact $artefact, array $previousUnitsPerArtefact): Collection
    {
        $changedTaskIds = $artefact->manufactureTasks()->get()
            ->filter(fn (ManufactureTask $manufactureTask) => isset($previousUnitsPerArtefact[$manufactureTask->id])
                && abs(ArtefactManufactureTask::exactUnits($previousUnitsPerArtefact[$manufactureTask->id]) - ArtefactManufactureTask::exactUnits($manufactureTask->pivot->units_per_artefact)) > 1e-9)
            ->pluck('id');

        return JobOrderItemTask::whereIn('manufacture_task_id', $changedTaskIds)
            ->whereHas('jobOrderItem', fn ($query) => $query->where('artefact_id', $artefact->id))
            ->whereHas('jobOrder', fn ($query) => $query->whereNotIn('state', [JobOrderStateEnum::RECEIVED, JobOrderStateEnum::NOT_RECEIVED]))
            ->where('quantity_made', '>', 0)
            ->with('jobOrder')
            ->get()
            ->map(fn (JobOrderItemTask $task) => $task->jobOrder->reference.' '.$artefact->code)
            ->unique()
            ->values();
    }
}
