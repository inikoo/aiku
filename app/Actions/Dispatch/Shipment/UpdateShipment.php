<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipment;

use App\Actions\Dispatch\Shipment\Hydrators\ShipmentHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\Dispatch\Shipment;

class UpdateShipment
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
            'code' => ['required', 'unique:tenant.shipments', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string']
        ];
    }

    public function action(Shipment $shipment, array $objectData): Shipment
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shipment, $validatedData);
    }
}
