<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 02:05:57 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Stock;

use App\Actions\Inventory\Stock\Hydrators\StockHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\Inventory\StockResource;
use App\Models\Inventory\Location;
use App\Models\Inventory\LocationStock;
use App\Models\Inventory\Stock;
use Lorisleiva\Actions\ActionRequest;

class MoveStockLocation
{
    use WithActionUpdate;

    public function handle(LocationStock $currentLocationStock, LocationStock $targetLocationStock, array $modelData): LocationStock
    {
        $this->update($currentLocationStock, [
            'quantity' => $currentLocationStock->quantity - $modelData['quantity'],
        ]);

        $this->update($targetLocationStock, [
            'quantity' => (float) $targetLocationStock->quantity + (float) $modelData['quantity'],
        ]);

        return $currentLocationStock;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['sometimes', 'required'],
        ];
    }

    public function action(LocationStock $currentLocationStock, LocationStock $targetLocationStock, $objectData): LocationStock
    {
        $this->setRawAttributes($objectData);
        $this->validateAttributes();

        return $this->handle($currentLocationStock, $targetLocationStock, $objectData);
    }

    public function asController(LocationStock $currentLocationStock, LocationStock $targetLocationStock, ActionRequest $request): LocationStock
    {
        $request->validate();
        return $this->handle($currentLocationStock, $targetLocationStock, $request->all());
    }
}
