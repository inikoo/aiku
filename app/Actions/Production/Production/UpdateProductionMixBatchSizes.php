<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 20:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\Production;

use App\Actions\OrgAction;
use App\Models\Production\Production;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

/**
 * The Production Manager's list of mix batch sizes recipes can choose from. A size taken off the list stays on
 * the recipes already using it.
 */
class UpdateProductionMixBatchSizes extends OrgAction
{
    /**
     * @param array{sizes: array<int, float|int|string>} $modelData
     */
    public function handle(Production $production, array $modelData): Production
    {
        $sizes = collect($modelData['sizes'])->map(fn ($size) => round((float) $size, 3))->unique()->sort()->values()->all();

        $production->update([
            'settings' => array_merge($production->settings ?? [], ['mix_batch_sizes_kg' => $sizes]),
        ]);

        return $production;
    }

    public function rules(): array
    {
        return [
            'sizes'   => ['required', 'array', 'min:1', 'max:20'],
            'sizes.*' => ['required', 'numeric', 'gt:0', 'max:99999', 'decimal:0,3', 'distinct'],
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'sizes.required'   => __('Keep at least one batch size'),
            'sizes.*.distinct' => __('Each batch size can only be on the list once'),
            'sizes.*.decimal'  => __('A batch size can have up to 3 decimals'),
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo(["org-supervisor.{$this->organisation->id}", "productions_rd.{$this->production->id}.edit"]);
    }

    public function action(Production $production, array $modelData): Production
    {
        $this->asAction = true;
        $this->initialisationFromProduction($production, $modelData);

        return $this->handle($production, $this->validatedData);
    }

    public function asController(Production $production, ActionRequest $request): Production
    {
        $this->initialisationFromProduction($production, $request);

        return $this->handle($production, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
