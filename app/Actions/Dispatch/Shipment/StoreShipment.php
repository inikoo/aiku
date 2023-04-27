<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipment;

use App\Actions\Dispatch\Shipment\Hydrators\ShipmentHydrateUniversalSearch;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\Shipment;
use App\Models\Marketing\OfferCampaign;
use App\Models\Marketing\Shop;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreShipment
{
    use AsAction;
    use WithAttributes;

    public function handle(DeliveryNote $parent, array $modelData): Shipment
    {
        /** @var Shipment $shipment */
        $shipment = $parent->shipments()->create($modelData);

        ShipmentHydrateUniversalSearch::dispatch($shipment);

        return $shipment;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:tenant.shippers', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string']
        ];
    }

    public function action(DeliveryNote $parent, array $objectData): Shipment
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData);
    }
}
