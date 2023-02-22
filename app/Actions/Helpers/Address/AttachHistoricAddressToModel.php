<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Feb 2023 12:52:34 Malaysia Time, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Address;

use App\Models\Dispatch\DeliveryNote;
use App\Models\Helpers\Address;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use Lorisleiva\Actions\Concerns\AsAction;


class AttachHistoricAddressToModel
{

    use AsAction;

    public function handle(
        Order|Invoice|DeliveryNote $model,
        Address $address,
        array $scopeData
    ): Address {
        $model->addresses()->attach($address->id, $scopeData);
        HydrateAddress::run($address);

        return $address;
    }
}
