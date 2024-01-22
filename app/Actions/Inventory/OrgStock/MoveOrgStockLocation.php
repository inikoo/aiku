<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 02:05:57 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock;

use App\Actions\Inventory\Warehouse\HydrateWarehouse;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Inventory\LocationOrgStock;
use Lorisleiva\Actions\ActionRequest;

class MoveOrgStockLocation
{
    use WithActionUpdate;

    public function handle(LocationOrgStock $currentLocationStock, LocationOrgStock $targetLocation, array $movementData): LocationOrgStock
    {
        $this->update($currentLocationStock, [
            'quantity' => $currentLocationStock->quantity - $movementData['quantity'],
        ]);

        $this->update($targetLocation, [
            'quantity' => (float) $targetLocation->quantity + (float) $movementData['quantity'],
        ]);

        HydrateWarehouse::run($currentLocationStock->location->warehouse);

        return $currentLocationStock;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['sometimes', 'required'],
        ];
    }

    public function action(LocationOrgStock $currentLocationStock, LocationOrgStock $targetLocation, $modelData): LocationOrgStock
    {
        $this->setRawAttributes($modelData);
        $this->validateAttributes();

        return $this->handle($currentLocationStock, $targetLocation, $modelData);
    }

    public function asController(LocationOrgStock $currentLocationStock, LocationOrgStock $targetLocation, ActionRequest $request): LocationOrgStock
    {
        $request->validate();
        return $this->handle($currentLocationStock, $targetLocation, $request->all());
    }
}
