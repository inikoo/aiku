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
use Lorisleiva\Actions\ActionRequest;

class SetPalletAsNotReceived extends OrgAction
{
    use WithActionUpdate;


    private Pallet $pallet;

    public function handle(Pallet $pallet): Pallet
    {

        data_set($modelData, 'state', PalletStateEnum::NOT_RECEIVED);
        data_set($modelData, 'status', PalletStatusEnum::NOT_RECEIVED);
        data_set($modelData, 'location_id', null);
        data_set($modelData, 'booked_in_at', null);
        data_set($modelData, 'set_as_not_received_at', now());


        return UpdatePallet::run($pallet, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.edit");
    }


    public function asController(Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->initialisationFromWarehouse($pallet->warehouse, $request);

        return $this->handle($pallet);
    }

    public function undo(Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->initialisationFromWarehouse($pallet->warehouse, $request);

        return $this->handle($pallet);
    }

    public function action(Pallet $pallet): Pallet
    {
        $this->asAction       = true;
        $this->initialisationFromWarehouse($pallet->warehouse, []);

        return $this->handle($pallet);
    }

    public function jsonResponse(Pallet $pallet): PalletResource
    {
        return new PalletResource($pallet);
    }
}
