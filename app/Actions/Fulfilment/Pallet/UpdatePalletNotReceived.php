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
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\Fulfilment\Pallet;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\ActionRequest;

class UpdatePalletNotReceived extends OrgAction
{
    use WithActionUpdate;


    private Pallet $pallet;

    public function handle(Pallet $pallet): Pallet
    {
        $modelData['state'] = PalletStateEnum::NOT_RECEIVED;

        return $this->update($pallet, $modelData, ['data']);
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

        return $this->handle($pallet);
    }

    public function action(Warehouse $warehouse, Pallet $pallet, array $modelData, int $hydratorsDelay = 0): Pallet
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromWarehouse($warehouse, $modelData);

        return $this->handle($pallet);
    }

    public function jsonResponse(Pallet $pallet): PalletResource
    {
        return new PalletResource($pallet);
    }
}
