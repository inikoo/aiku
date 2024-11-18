<?php
/*
 * author Arya Permana - Kirin
 * created on 04-11-2024-09h-23m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Ordering\PurgedOrder;

use App\Actions\Ordering\Purge\Hydrators\PurgeHydratePurgedOrders;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Ordering\Purge\PurgedOrderStatusEnum;
use App\Models\Ordering\PurgedOrder;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdatePurgedOrder extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;
    public function handle(PurgedOrder $purgedOrder, $modelData): PurgedOrder
    {

        $purgedOrder = $this->update($purgedOrder, $modelData);

        PurgeHydratePurgedOrders::dispatch($purgedOrder->purge);

        return $purgedOrder;
    }

    public function authorize(ActionRequest $request)
    {
        if ($this->asAction) {
            return true;
        }

        return true;
    }

    public function rules()
    {
        $rules =  [
            'status'              => ['sometimes', Rule::enum(PurgedOrderStatusEnum::class)],
            'note'                => ['sometimes', 'string']
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    public function action(PurgedOrder $purgedOrder, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true)
    {
        $this->asAction = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($purgedOrder->purge->shop, $modelData);
        return $this->handle($purgedOrder, $modelData);
    }

    public function asController(PurgedOrder $purgedOrder, ActionRequest $request): PurgedOrder
    {
        $this->initialisationFromShop($purgedOrder->purge->shop, $request);
        return $this->handle($purgedOrder, $this->validatedData);
    }
}
