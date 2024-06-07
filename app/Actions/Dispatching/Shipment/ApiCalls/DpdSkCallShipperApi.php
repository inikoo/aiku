<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Shipment\ApiCalls;

use App\Models\Dispatching\Shipper;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DpdSkCallShipperApi
{
    use AsAction;
    use WithAttributes;
    public function handle(Request $request, Shipper $shipper): array
    {
        $apiUrl         = "https://api.dpdportal.sk/shipment";
        $shipmentParams = DpdSkShipmentParameters::run($request, $shipper);
        $params         = array(
            'jsonrpc' => '2.0',
            'method'  => 'create',
            'params'  => array(
                'DPDSecurity' => array(
                    'SecurityToken' => array(
                        'ClientKey' => $shipper->data['apiKey'],
                        'Email'     => $shipper->email,
                    ),
                ),
                'shipment'    => [$shipmentParams],
            ),
            'id'      => 'null',
        );
        return ProsesApiCalls::run($apiUrl, ["Content-Type: application/json"], json_encode($params));
    }
}
