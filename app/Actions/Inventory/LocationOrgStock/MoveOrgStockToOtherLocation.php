<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 29 Aug 2024 00:47:34 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\LocationOrgStock;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Inventory\LocationOrgStock;
use Lorisleiva\Actions\ActionRequest;

class MoveOrgStockToOtherLocation extends OrgAction
{
    use WithActionUpdate;
    use WithLocationOrgStockActionAuthorisation;



    public function handle(LocationOrgStock $currentLocationStock, LocationOrgStock $targetLocation, array $movementData): LocationOrgStock
    {
        $this->update($currentLocationStock, [
            'quantity' => $currentLocationStock->quantity - $movementData['quantity'],
        ]);

        $this->update($targetLocation, [
            'quantity' => (float) $targetLocation->quantity + (float) $movementData['quantity'],
        ]);


        return $currentLocationStock;
    }

    public function rules(): array
    {
        return [
            'quantity' => [ 'required','numeric','gt:0'],
        ];
    }

    public function action(LocationOrgStock $currentLocationStock, LocationOrgStock $targetLocation, array $modelData): LocationOrgStock
    {
        $this->asAction = true;
        $this->initialisation($currentLocationStock->location->organisation, $modelData);
        return $this->handle($currentLocationStock, $targetLocation, $this->validatedData);
    }

    public function asController(LocationOrgStock $locationOrgStock, LocationOrgStock $targetLocation, ActionRequest $request): LocationOrgStock
    {
        $this->initialisation($locationOrgStock->location->organisation, $request);
        return $this->handle($locationOrgStock, $targetLocation, $this->validatedData);
    }
}
