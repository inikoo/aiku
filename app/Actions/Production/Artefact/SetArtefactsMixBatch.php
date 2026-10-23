<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 22 Oct 2026 Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\Artefact;

use App\Actions\OrgAction;
use App\Models\Production\ArtefactManufactureTask;
use App\Models\Production\ManufactureTask;
use App\Models\Production\Production;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class SetArtefactsMixBatch extends OrgAction
{
    /**
     * Sets the mix batch size on every kg recipe step of the selected artefacts; open jobs read it from the recipe.
     *
     * @param  array{artefacts: array<int, int>, mix_batch_kg: float|null}  $modelData
     */
    public function handle(Production $production, array $modelData): int
    {
        return ArtefactManufactureTask::whereIn('artefact_id', $production->artefacts()->whereIn('id', $modelData['artefacts'])->select('id'))
            ->whereIn('manufacture_task_id', ManufactureTask::where('production_id', $production->id)->where('is_recorded_in_kg', true)->select('id'))
            ->update(['mix_batch_kg' => $modelData['mix_batch_kg']]);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo(["org-supervisor.{$this->organisation->id}", "productions_rd.{$this->production->id}.edit"]);
    }

    public function rules(): array
    {
        return [
            'artefacts'    => ['required', 'array', 'min:1'],
            'artefacts.*'  => ['integer'],
            'mix_batch_kg' => ['present', 'nullable', Rule::in($this->production->mixBatchSizesKg())],
        ];
    }

    public function action(Production $production, array $modelData): int
    {
        $this->asAction = true;
        $this->initialisationFromProduction($production, $modelData);

        return $this->handle($production, $this->validatedData);
    }

    public function asController(Production $production, ActionRequest $request): int
    {
        $this->initialisationFromProduction($production, $request);

        return $this->handle($production, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
