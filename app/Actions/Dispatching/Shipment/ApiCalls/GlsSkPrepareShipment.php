<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Shipment\ApiCalls;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use stdClass;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use ReflectionException;
use Yasumi\Yasumi;

class GlsSkPrepareShipment
{
    use AsAction;
    use WithAttributes;
    public function handle($shipper, $request, $pickUp, $shipTo, $parcelsData, $cash_on_delivery): array
    {
        $parcels = [];
        $parcel  = new StdClass();

        $organisation = $shipper->organisation;

        $shipTo = array_filter($shipTo);

        $services = [];
        if (!empty($cash_on_delivery)) {
            $parcel->CODAmount    = $cash_on_delivery['amount'];
            $parcel->CODReference = $request->get('reference');
            $service1             = new StdClass();
            $service1->Code       = "COD";
            $services[]           = $service1;
        }


        $organisation_postal_code = trim(Arr::get($organisation->data['address'], 'sorting_code').' '.Arr::get($organisation->data['address'], 'postal_code'));
        if (Arr::get($organisation->data['address'], 'country_code') == 'SK') {
            $organisation_postal_code = preg_replace('/SK-?/i', '', $organisation_postal_code);
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
        $pickupAddress->ContactName      = Arr::get($organisation->data, 'contact');
        $pickupAddress->ContactPhone     = Arr::get($organisation->data, 'phone');
        $pickupAddress->ContactEmail     = Arr::get($organisation->data, 'email');
        $pickupAddress->Name             = Arr::get($organisation->data, 'organization');
        $pickupAddress->Street           = trim(Arr::get($organisation->data['address'], 'address_line_1').' '.Arr::get($organisation->data['address'], 'address_line_2'));
        $pickupAddress->City             = Arr::get($organisation->data['address'], 'locality');
        $pickupAddress->ZipCode          = $organisation_postal_code;
        $pickupAddress->CountryIsoCode   = Arr::get($organisation->data['address'], 'country_code');
        $parcel->PickupAddress           = $pickupAddress;
        $parcel->PickupDate              = gmdate('Y-m-d');
        $parcel->ServiceList             = $services;

        $parcels[] = $parcel;


        return $parcels;
    }

    private function get_pick_up_date(Carbon $pickup_date): Carbon
    {
        if ($pickup_date->isWeekend() or $this->is_bank_holiday($pickup_date)) {
            return $this->get_pick_up_date($pickup_date->addDay());
        }
        return $pickup_date;
    }

    private function is_bank_holiday($date): bool
    {
        $formatted_date = $date->format('Y-m-d');
        try {
            $holidays = Yasumi::create('Slovakia', $date->format('Y'));
            foreach ($holidays as $day) {
                if ($day == $formatted_date and in_array(
                    $day->getType(),
                    [
                            'bank',
                            'official'
                        ]
                )) {
                    return true;
                }
            }
            return false;
        } catch (ReflectionException $e) {
            return false;
        }
    }
}
