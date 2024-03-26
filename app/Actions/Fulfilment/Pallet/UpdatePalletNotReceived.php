<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Fulfilment\PalletDelivery\UpdatePalletDeliveryStateFromItems;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\Fulfilment\Pallet;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\ActionRequest;

class UpdatePalletNotReceived extends OrgAction
{
    use WithActionUpdate;


    private Pallet $pallet;

    public function handle(Pallet $pallet, $state): Pallet
    {
        $modelData['state']       = $state;
        $modelData['location_id'] = null;

        $pallet = $this->update($pallet, $modelData, ['data']);

        UpdatePalletDeliveryStateFromItems::run($pallet->palletDelivery);

        return $pallet;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.edit");
    }


    public function asController(Warehouse $warehouse, Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($pallet, PalletStateEnum::NOT_RECEIVED);
    }

    public function undo(Warehouse $warehouse, Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($pallet, PalletStateEnum::RECEIVED);
    }

    public function action(Warehouse $warehouse, Pallet $pallet, $state, int $hydratorsDelay = 0): Pallet
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromWarehouse($warehouse, []);

        return $this->handle($pallet, $state);
    }

    public function jsonResponse(Pallet $pallet): PalletResource
    {
        return new PalletResource($pallet);
    }
}
