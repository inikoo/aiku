<?php

/*
 * author Arya Permana - Kirin
 * created on 20-11-2024-10h-37m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Dispatching\PickingRoute;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Dispatching\PickingRoute;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\ActionRequest;

class StorePickingRoute extends OrgAction
{
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(Warehouse $warehouse, array $modelData): PickingRoute
    {
        data_set($modelData, 'group_id', $warehouse->group_id);
        data_set($modelData, 'organisation_id', $warehouse->organisation_id);
        $pickingRoute = $warehouse->pickingRoutes()->create($modelData);

        return $pickingRoute;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string']
        ];

        if (!$this->strict) {
            $rules              = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }


    /**
     * @throws \Throwable
     */
    public function action(Warehouse $warehouse, array $modelData, int $hydratorsDelay = 0, bool $strict = true): PickingRoute
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisationFromWarehouse($warehouse, $modelData);

        return $this->handle($warehouse, $this->validatedData);
    }
}
