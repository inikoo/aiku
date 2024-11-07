<?php
/*
 * author Arya Permana - Kirin
 * created on 04-11-2024-09h-30m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Ordering\Purge;

use App\Actions\Ordering\Purge\Hydrators\PurgeHydratePurgedOrders;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Ordering\Purge\PurgeStateEnum;
use App\Enums\Ordering\Purge\PurgeTypeEnum;
use App\Models\Ordering\Purge;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdatePurge extends OrgAction
{
    use WithActionUpdate;

    public function handle(Purge $purge, $modelData): Purge
    {
        if (Arr::get($modelData, 'state') == PurgeStateEnum::CANCELLED) {
            data_set($modelData, 'cancelled_at', now());
        }
        $purge = $this->update($purge, $modelData);

        PurgeHydratePurgedOrders::dispatch($purge);

        return $purge;
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
        return [
            'state'             => ['sometimes', Rule::enum(PurgeStateEnum::class)],
            'type'              => ['sometimes', Rule::enum(PurgeTypeEnum::class)],
            'scheduled_at'      => ['sometimes', 'date'],
            'cancelled_at'      => ['sometimes', 'date'],
            'start_at'          => ['sometimes', 'date'],
            'end_at'            => ['sometimes', 'date'],
        ];
    }

    public function asController(Purge $purge, ActionRequest $request)
    {
        $this->initialisationFromShop($purge->shop, $request);
        return $this->handle($purge, $this->validatedData);
    }

    public function action(Purge $purge, array $modelData)
    {
        $this->asAction = true;
        $this->initialisationFromShop($purge->shop, $modelData);
        return $this->handle($purge, $this->validatedData);
    }
}
