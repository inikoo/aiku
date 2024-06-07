<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Shipment\ApiCalls;

use App\Models\Dispatching\Shipper;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class ApcGbGetLabel
{
    use AsAction;
    use WithAttributes;
    public function handle($labelID, Shipper $shipper): false|string
    {
        $headers = [
        "remote-user: Basic ".base64_encode($shipper->email.':'.$shipper->code),
        "Content-Type: application/json"
        ];
        $apiResponse = ProsesApiCalls::run(
            'https://apc.hypaship.com/api/3.0/Orders/'.$labelID.'.json',
            $headers,
            json_encode([]),
            'GET'
        );

        return base64_decode($apiResponse['data']['Orders']['Order']['Label']['Content']);
    }
}
