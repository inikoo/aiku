<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Shipment\ApiCalls;

use App\Models\Dispatching\Shipment;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class WhistlGbShipmentParameters
{
    use AsAction;
    use WithAttributes;
    public function handle(Request $request): Shipment
    {
        $parcels          = json_decode($request->get('parcels'), true);
        $shipTo           = json_decode($request->get('ship_to'), true);
        $pickUp           = json_decode($request->get('pick_up'), true);
        $cash_on_delivery = json_decode($request->get('cod', '{}'), true);

        return PostmenPrepareShipment::run($request, $pickUp, $shipTo, $parcels, $cash_on_delivery);
    }

}
