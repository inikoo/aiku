<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Shipment\ApiCalls;

use App\Models\Dispatching\DeliveryNote;
use App\Models\Dispatching\Shipment;
use App\Models\Dispatching\Shipper;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Illuminate\Support\Arr;

class WhistlGbCallShipperApi
{
    use AsAction;
    use WithAttributes;
    public function handle(DeliveryNote $deliveryNote, Shipper $shipper): Shipment
    {
        $apiUrl = "https://api.dpd.co.uk/";
        if (Arr::get($shipper->data, 'geoSession') == '' or (gmdate('U') - Arr::get($shipper->data, 'geoSessionDate', 0) > 43200)) {
            $this->login($apiUrl, $shipper);
        }
        $accept = 'application/json';
        $header =  [
            "GeoSession: ".Arr::get($shipper->data, 'geoSession'),
            "Accept: ".$accept,
            "Content-Type: application/json",
            'GeoClient: account/' .$shipper->data['account_number']
        ];
        $output      = [];
        $apiResponse = ProsesApiCalls::run($apiUrl.$deliveryNote->data['shipmentId'].'/label', $header, json_encode([]), 'GET', $output);
        return $apiResponse['data'];
    }


    public function login($apiUrl, $shipper): Shipper
    {
        $headers = [
            "Authorization: Basic ".base64_encode($shipper->code.':'.$shipper->data['password']),
            "Content-Type: application/json",
            "Accept: application/json",
            'GeoClient: account/'.$shipper->data['account_number']
        ];

        $params = [];

        $apiResponse = ProsesApiCalls::run(
            $apiUrl.'user?action=login',
            $headers,
            json_encode($params)
        );

        if ($apiResponse['status'] == 200 and !empty($apiResponse['data']['data']['geoSession'])) {
            $shipper                   = $shipper->data;
            $shipper['geoSession']     = $apiResponse['data']['data']['geoSession'];
            $shipper['geoSessionDate'] = gmdate('U');
            $shipper->data             = $shipper;
            $shipper->save();

        }
        return $shipper;
    }
}
