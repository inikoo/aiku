<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipment;

use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\Shipment;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class CallApcgbShipperApi
{
    use AsAction;
    use WithAttributes;
    public function handle(DeliveryNote $deliveryNote, Shipper $shipper, $type): Shipment
    {
        $password="";
        $apiUrl = "https://apc.hypaship.com/api/3.0/";
        $header = [
            "remote-user: Basic ".base64_encode($shipper->email.':'.$password),
            "Content-Type: application/json"
        ];
        $params = array(
            'Orders' => [
                'Order' => json_encode($deliveryNote)
            ]
        );
        if ($type == 'apiCall') {
            $apiResponse =  $this->callApi($apiurl.'Orders.json',$header,json_encode($params));
        } else {
            $apiResponse =  $this->callApi($apiurl.'Tracks/'.$shipment->tracking.'.json?searchtype=CarrierWaybill&history=Yes',$header, "[]", 'GET');
        }

        return $apiResponse;
    }

    public function callApi ($url, $headers, $params, $method = 'POST', $result_encoding = 'json')
    {
        $curl = curl_init();


        curl_setopt_array(
            $curl, array(
                     CURLOPT_URL            => $url,
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_ENCODING       => "",
                     CURLOPT_MAXREDIRS      => 10,
                     CURLOPT_TIMEOUT        => 0,
                     CURLOPT_FOLLOWLOCATION => true,
                     CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                     CURLOPT_CUSTOMREQUEST  => $method,
                     CURLOPT_POSTFIELDS     => $params,
                     CURLOPT_HTTPHEADER     => $headers,
                 )
        );


        $raw_response = curl_exec($curl);

        if ($raw_response == 'Unauthorized') {
            $response['errors'][] = ['fail' => 'Unauthorized'];
            $response['status']   = 530;

            return $response;
        }


        if ($result_encoding == 'xml') {
            $data = json_decode(json_encode(simplexml_load_string($raw_response)), true);
        } elseif ($result_encoding == 'json') {
            $data = json_decode($raw_response, true);
        } else {
            $data = $raw_response;
        }


        $response = [
            'status' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
            'data'   => $data
        ];

        if ($raw_response === false) {
            $response['errors'][] = ['curl_fail' => curl_error($curl).' ('.curl_errno($curl).')'];
            $response['status']   = 530;
            curl_close($curl);

            return $response;
        }
        curl_close($curl);

        if ($data == null) {
            $response['errors'][] = ['fail' => 'The API server returned an empty, unknown, or unexplained response'];
            $response['status']   = 530;
        }

        return $response;
    }
    
}
