<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\Fulfilment\Pallet;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class SetPalletAsLost extends OrgAction
{
    use WithActionUpdate;


    private Pallet $pallet;

    public function handle(Pallet $pallet, $modelData): Pallet
    {
        data_set($modelData, 'state', PalletStateEnum::LOST);
        data_set($modelData, 'status', PalletStatusEnum::INCIDENT);
        data_set($modelData, 'set_as_incident_at', now());

        data_set($modelData, 'incident_report', [
            [
                'type'        => PalletStateEnum::LOST->value,
                'message'     => Arr::get($modelData, 'message'),
                'reporter_id' => request()->user()->id,
                'date'        => now()
            ]
        ]);

        $pallet = UpdatePallet::run($pallet, $modelData, ['data']);

        return $pallet;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.edit");
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string']
        ];
    }

    public function asController(Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->initialisationFromWarehouse($pallet->warehouse, $request);

        return $this->handle($pallet, $this->validatedData);
    }

    public function action(Pallet $pallet): Pallet
    {
        $this->asAction       = true;
        $this->initialisationFromWarehouse($pallet->warehouse, []);

        return $this->handle($pallet, $this->validatedData);
    }

    public function jsonResponse(Pallet $pallet): PalletResource
    {
        return new PalletResource($pallet);
    }
}
