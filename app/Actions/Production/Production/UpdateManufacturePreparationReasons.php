<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 17:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\Production;

use App\Actions\OrgAction;
use App\Models\Production\ManufacturePreparationReason;
use App\Models\Production\Production;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

/**
 * The Production Manager's list of Preparation reasons, in the order given. A reason left out is retired, so the
 * sessions that used it keep their reason; "Other" always stays last.
 */
class UpdateManufacturePreparationReasons extends OrgAction
{
    /**
     * @param array{reasons: array<int, array{id?: int|null, name: string}>} $modelData
     */
    public function handle(Production $production, array $modelData): void
    {
        DB::transaction(function () use ($production, $modelData) {
            $current = ManufacturePreparationReason::forProduction($production)->reject(fn (ManufacturePreparationReason $reason) => $reason->is_other)->keyBy('id');
            $kept    = [];

            foreach (array_values($modelData['reasons']) as $position => $reasonData) {
                $reason = $current->get($reasonData['id'] ?? 0) ?? new ManufacturePreparationReason([
                    'group_id'        => $production->group_id,
                    'organisation_id' => $production->organisation_id,
                    'production_id'   => $production->id,
                ]);
                $reason->fill(['name' => trim($reasonData['name']), 'position' => $position])->save();
                $kept[] = $reason->id;
            }

            $current->except($kept)->each(fn (ManufacturePreparationReason $reason) => $reason->delete());
        });
    }

    public function rules(): array
    {
        return [
            'reasons'        => ['present', 'array', 'max:20'],
            'reasons.*.id'   => ['sometimes', 'nullable', 'integer', Rule::exists('manufacture_preparation_reasons', 'id')->where('production_id', $this->production->id)->where('is_other', false)->whereNull('deleted_at')],
            'reasons.*.name' => ['required', 'string', 'max:120', 'distinct:ignore_case'],
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'reasons.*.name.required' => __('Every reason needs a name'),
            'reasons.*.name.distinct' => __('Each reason can only be on the list once'),
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo([
            'org-supervisor.'.$this->organisation->id,
            "productions_operations.{$this->production->id}.orchestrate",
        ]);
    }

    public function action(Production $production, array $modelData): void
    {
        $this->asAction = true;
        $this->initialisationFromProduction($production, $modelData);

        $this->handle($production, $this->validatedData);
    }

    public function asController(Production $production, ActionRequest $request): void
    {
        $this->initialisationFromProduction($production, $request);

        $this->handle($production, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
