<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipment;

use App\Actions\Dispatch\Shipment\Hydrators\ShipmentHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dispatch\Shipment;

class UpdateShipment extends OrgAction
{
    use WithActionUpdate;

    public function handle(Shipment $shipment, array $modelData): Shipment
    {
        $shipment = $this->update($shipment, $modelData, ['data']);
        ShipmentHydrateUniversalSearch::dispatch($shipment);
        return $shipment;
    }

    public function rules(): array
    {
        return [
            'reference' => ['required',  'max:64', 'string']
        ];
    }

    public function action(Shipment $shipment, array $modelData): Shipment
    {
        $this->initialisation($shipment->organisation, $modelData);
        return $this->handle($shipment, $this->validatedData);
    }
}
