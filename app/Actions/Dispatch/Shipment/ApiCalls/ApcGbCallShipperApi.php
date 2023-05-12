<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipment\ApiCalls;

use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\Shipper;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class ApcGbCallShipperApi
{
    use AsAction;
    use WithAttributes;
    public function handle(DeliveryNote $deliveryNote, Shipper $shipper): array
    {
        $apiUrl = "https://apc.hypaship.com/api/3.0/";
        $header = [
            "remote-user: Basic ".base64_encode($shipper->email.':'.$shipper->code),
            "Content-Type: application/json"
        ];
        $params = array(
            'Orders' => [
                'Order' => $deliveryNote
            ]
        );
        return ProsesApiCalls::run($apiUrl.'Orders.json',$header,json_encode($params));
    }
}
