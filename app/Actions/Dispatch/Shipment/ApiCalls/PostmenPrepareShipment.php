<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipment\ApiCalls;

use Illuminate\Support\Arr;
use stdClass;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class GlsSkPrepareShipment
{
    use AsAction;
    use WithAttributes;
    public function handle($shipper, $request, $pickUp, $shipTo, $parcelsData, $cash_on_delivery): array
    {
        $parcels = [];
        $parcel  = new StdClass();

        $tenant = $shipper->tenant;

        $shipTo = array_filter($shipTo);

        $services = [];
        if (!empty($cash_on_delivery)) {
            $parcel->CODAmount    = $cash_on_delivery['amount'];
            $parcel->CODReference = $request->get('reference');
            $service1             = new StdClass();
            $service1->Code       = "COD";
            $services[]           = $service1;
        }


        $tenant_postal_code = trim(Arr::get($tenant->data['address'], 'sorting_code').' '.Arr::get($tenant->data['address'], 'postal_code'));
        if (Arr::get($tenant->data['address'], 'country_code') == 'SK') {
            $tenant_postal_code = preg_replace('/SK-?/i', '', $tenant_postal_code);
        }


        $parcel->ClientNumber            = $shipper->credentials['client_number'];
        $parcel->ClientReference         = $request->get('reference');
        $parcel->Content                 = $request->get('note');
        $parcel->Count                   = count($parcelsData);
        $deliveryAddress                 = new StdClass();
        $deliveryAddress->ContactEmail   = Arr::get($shipTo, 'email');
        $deliveryAddress->ContactName    = Arr::get($shipTo, 'contact');
        $deliveryAddress->ContactPhone   = Arr::get($shipTo, 'phone');
        $deliveryAddress->Name           = Arr::get($shipTo, 'organization', Arr::get($shipTo, 'contact'));
        $deliveryAddress->Street         = trim(Arr::get($shipTo, 'address_line_1').' '.Arr::get($shipTo, 'address_line_2'));
        $deliveryAddress->City           = Arr::get($shipTo, 'locality');
        $deliveryAddress->ZipCode        = trim(Arr::get($shipTo, 'sorting_code').' '.Arr::get($shipTo, 'postal_code'));
        $deliveryAddress->CountryIsoCode = Arr::get($shipTo, 'country_code');
        $parcel->DeliveryAddress         = $deliveryAddress;
        $pickupAddress                   = new StdClass();
        $pickupAddress->ContactName      = Arr::get($tenant->data, 'contact');
        $pickupAddress->ContactPhone     = Arr::get($tenant->data, 'phone');
        $pickupAddress->ContactEmail     = Arr::get($tenant->data, 'email');
        $pickupAddress->Name             = Arr::get($tenant->data, 'organization');
        $pickupAddress->Street           = trim(Arr::get($tenant->data['address'], 'address_line_1').' '.Arr::get($tenant->data['address'], 'address_line_2'));
        $pickupAddress->City             = Arr::get($tenant->data['address'], 'locality');
        $pickupAddress->ZipCode          = $tenant_postal_code;
        $pickupAddress->CountryIsoCode   = Arr::get($tenant->data['address'], 'country_code');
        $parcel->PickupAddress           = $pickupAddress;
        $parcel->PickupDate              = gmdate('Y-m-d');
        $parcel->ServiceList             = $services;

        $parcels[] = $parcel;


        return $parcels;
    }


}
