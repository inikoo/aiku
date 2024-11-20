<?php
/*
 * author Arya Permana - Kirin
 * created on 20-11-2024-10h-44m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Dispatching\PickingRoute;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dispatching\PickingRoute;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\ActionRequest;

class UpdatePickingRoute extends OrgAction
{
    use WithNoStrictRules;
    use WithActionUpdate;
    /**
     * @throws \Throwable
     */
    public function handle(PickingRoute $pickingRoute, array $modelData): PickingRoute
    {
        $pickingRoute = $this->update($pickingRoute, $modelData);

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
            'name' => ['sometimes', 'string']
        ];

        if (!$this->strict) {
            $rules              = $this->noStrictUpdateRules($rules);
        }

        return $rules;
    }


    /**
     * @throws \Throwable
     */
    public function action(PickingRoute $pickingRoute, array $modelData, int $hydratorsDelay = 0, bool $strict = true): PickingRoute
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisationFromWarehouse($pickingRoute->warehouse, $modelData);

        return $this->handle($pickingRoute, $this->validatedData);
    }
}
