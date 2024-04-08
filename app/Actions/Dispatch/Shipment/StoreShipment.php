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
use App\Actions\OrgAction;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\Shipment;
use App\Models\Dispatch\Shipper;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreShipment extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public function handle(DeliveryNote $deliveryNote, Shipper $shipper, array $modelData): Shipment
    {

        $modelData=array_merge(
            $modelData,
            [
                'group_id'        => $deliveryNote->group_id,
                'organisation_id' => $deliveryNote->organisation_id,
                'shop_id'         => $deliveryNote->shop_id,
                'customer_id'     => $deliveryNote->customer_id,
            ]
        );

        /** @var Shipment $shipment */
        $shipment                =match($shipper->api_shipper) {
            'apc-gb'=> ApcGbCallShipperApi::run($deliveryNote, $shipper),
            'dpd-gb'=> DpdGbCallShipperApi::run($deliveryNote, $shipper),
            'dpd-sk'=> DpdSkCallShipperApi::run($deliveryNote, $shipper),
            'pst-mn'=> PostmenCallShipperApi::run($deliveryNote, $shipper),
            'whl-gb'=> WhistlGbCallShipperApi::run($deliveryNote, $shipper),
            default => $shipper->shipments()->create($modelData),
        };

        $shipment->deliveryNotes()->attach($deliveryNote->id);
        $shipment->refresh();



        ShipmentHydrateUniversalSearch::dispatch($shipment);

        return $shipment;
    }

    public function rules(): array
    {
        return [
            'reference' => ['required',  'max:64', 'string']
        ];
    }

    public function action(DeliveryNote $deliveryNote, Shipper $shipper, array $modelData): Shipment
    {
        $this->initialisation($deliveryNote->organisation, $modelData);
        return $this->handle($deliveryNote, $shipper, $this->validatedData);
    }
}
