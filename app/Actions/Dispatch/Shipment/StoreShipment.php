<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipment;

use App\Actions\Dispatch\Shipment\ApiCalls\ApcGbCallShipperApi;
use App\Actions\Dispatch\Shipment\ApiCalls\DpdGbCallShipperApi;
use App\Actions\Dispatch\Shipment\ApiCalls\DpdSkCallShipperApi;
use App\Actions\Dispatch\Shipment\ApiCalls\PostmenCallShipperApi;
use App\Actions\Dispatch\Shipment\ApiCalls\WhistlGbCallShipperApi;
use App\Actions\Dispatch\Shipment\Hydrators\ShipmentHydrateUniversalSearch;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\Shipment;
use App\Models\Dispatch\Shipper;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreShipment
{
    use AsAction;
    use WithAttributes;

    public function handle(DeliveryNote $deliveryNote, Shipper $shipper, array $modelData): Shipment
    {
        $modelData['shipper_id'] = $shipper->id;
        $modelData['data']       = $deliveryNote;
        $shipment                =match($shipper->api_shipper) {
            'apc-gb'=> ApcGbCallShipperApi::run($deliveryNote, $shipper),
            'dpd-gb'=> DpdGbCallShipperApi::run($deliveryNote, $shipper),
            'dpd-sk'=> DpdSkCallShipperApi::run($deliveryNote, $shipper),
            'pst-mn'=> PostmenCallShipperApi::run($deliveryNote, $shipper),
            'whl-gb'=> WhistlGbCallShipperApi::run($deliveryNote, $shipper),
            default => $shipper->shipments()->create($modelData),
        };
        ShipmentHydrateUniversalSearch::dispatch($shipment);

        return $shipment;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:shipments', 'between:2,9', 'alpha']
        ];
    }

    public function action(DeliveryNote $deliveryNote, Shipper $shipper, array $objectData): Shipment
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($deliveryNote, $shipper, $validatedData);
    }
}
