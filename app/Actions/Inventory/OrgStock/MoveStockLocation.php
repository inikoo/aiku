<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 02:05:57 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock;

use App\Actions\Inventory\Warehouse\HydrateWarehouse;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Inventory\LocationStock;
use Lorisleiva\Actions\ActionRequest;

class MoveStockLocation
{
    use WithActionUpdate;

    public function handle(LocationStock $currentLocationStock, LocationStock $targetLocation, array $movementData): LocationStock
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

    public function action(LocationStock $currentLocationStock, LocationStock $targetLocation, $modelData): LocationStock
    {
        $this->setRawAttributes($modelData);
        $this->validateAttributes();

        return $this->handle($currentLocationStock, $targetLocation, $modelData);
    }

    public function asController(LocationStock $currentLocationStock, LocationStock $targetLocation, ActionRequest $request): LocationStock
    {
        $request->validate();
        return $this->handle($currentLocationStock, $targetLocation, $request->all());
    }
}
