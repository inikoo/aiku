<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 08 Aug 2026 22:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\Artefact;

use App\Actions\OrgAction;
use App\Actions\Production\JobOrderItemTask\GenerateJobOrderItemTasks;
use App\Enums\Production\JobOrder\JobOrderStateEnum;
use App\Models\Production\JobOrderItem;
use App\Models\Production\Artefact;
use App\Models\Production\ManufactureTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class AttachManufactureTaskToArtefact extends OrgAction
{
    /** @var array<int, string> */
    private array $jobsKeepingTheirCount = [];

    public function handle(Artefact $artefact, array $modelData): Artefact
    {
        $step = [
            'position'           => $modelData['position'] ?? 1,
            'units_per_artefact' => $modelData['units_per_artefact'] ?? 1,
        ];
        if (array_key_exists('standard_rate', $modelData)) {
            $step['standard_rate'] = $modelData['standard_rate'];
        }

        $previousUnits = $artefact->manufactureTasks()->get()->mapWithKeys(fn (ManufactureTask $manufactureTask) => [$manufactureTask->id => $manufactureTask->pivot->units_per_artefact])->all();

        $artefact->manufactureTasks()->syncWithoutDetaching([$modelData['manufacture_task_id'] => $step]);

        JobOrderItem::where('artefact_id', $artefact->id)
            ->whereHas('jobOrder', fn ($query) => $query->whereNotIn('state', [JobOrderStateEnum::RECEIVED, JobOrderStateEnum::NOT_RECEIVED]))
            ->each(fn (JobOrderItem $jobOrderItem) => GenerateJobOrderItemTasks::run($jobOrderItem, [], $previousUnits));

        $this->jobsKeepingTheirCount = GenerateJobOrderItemTasks::make()->jobsKeepingTheirCount($artefact, $previousUnits)->all();

        return $artefact;
    }

    public function rules(): array
    {
        return [
            'manufacture_task_id' => [
                'required',
                Rule::exists('manufacture_tasks', 'id')->where('production_id', $this->artefact->production_id),
            ],
            'position'            => ['sometimes', 'integer', 'min:1'],
            'units_per_artefact'  => ['sometimes', 'numeric', 'gt:0'],
            'standard_rate'       => ['sometimes', 'nullable', 'numeric', 'gt:0'],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->has('standard_rate')) {
            return $request->user()->authTo(["org-supervisor.{$this->organisation->id}", "productions_rd.{$this->production->id}.edit"]);
        }

        return $request->user()->authTo([
            'org-supervisor.'.$this->organisation->id,
            'productions-view.'.$this->organisation->id,
            "productions_operations.{$this->production->id}.view",
            "productions_operations.{$this->production->id}.orchestrate",
        ]);
    }

    private Artefact $artefact;

    public function action(Artefact $artefact, array $modelData): Artefact
    {
        $this->asAction = true;
        $this->artefact = $artefact;
        $this->initialisation($artefact->organisation, $modelData);

        return $this->handle($artefact, $this->validatedData);
    }

    public function asController(Artefact $artefact, ActionRequest $request): Artefact
    {
        $this->artefact = $artefact;
        $this->initialisationFromProduction($artefact->production, $request);

        return $this->handle($artefact, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        if (!$this->jobsKeepingTheirCount) {
            return back();
        }

        return back()->with('notification', [
            'status'      => 'warning',
            'title'       => __('Jobs already worked on keep their old count'),
            'description' => __('Work is already recorded on these with the previous units, check them on the job order: :jobs', [
                'jobs' => implode(', ', $this->jobsKeepingTheirCount),
            ]),
        ]);
    }
}
