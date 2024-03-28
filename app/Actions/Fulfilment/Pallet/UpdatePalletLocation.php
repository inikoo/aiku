<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Fulfilment\Pallet\Hydrators\HydrateMovementPallet;
use App\Actions\Fulfilment\StoredItem\Hydrators\StoredItemHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\Pallet;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class UpdatePalletLocation extends OrgAction
{
    use WithActionUpdate;


    private Pallet $pallet;

    public function handle(Location $location, Pallet $pallet): Pallet
    {
        $lastLocationId = $pallet->location_id;

        $pallet = $this->update($pallet, [
            'location_id' => $location->id
        ]);

        StoredItemHydrateUniversalSearch::dispatch($pallet);
        HydrateMovementPallet::dispatch($pallet, $lastLocationId);

        return $pallet;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, Location $location, Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->pallet = $pallet;
        $this->initialisationFromFulfilment($pallet->fulfilment, $request);

        return $this->handle($location, $pallet);
    }

    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->pallet = $pallet;
        $this->initialisationFromWarehouse($warehouse, $request);

        $location = Location::find($request->only('location_id'));

        return $this->handle($location, $pallet);
    }
}
