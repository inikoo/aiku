<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Feb 2023 14:26:03 Malaysia Time, Pantai  art fetch:orders aw -s 2640116Lembeng, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Address;

use App\Models\Accounting\Invoice;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Helpers\Address;
use App\Models\Ordering\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHistoricAddressToModel
{
    use AsAction;

    public function handle(
        Order|Invoice|DeliveryNote $model,
        Address $currentAddress,
        Address $address,
        array $scopeData
    ): Address {
        $model->addresses()->attach([$address->id], $scopeData);
        $model->addresses()->detach([$currentAddress->id]);

        HydrateAddress::run($address);
        HydrateAddress::run($currentAddress);
        if ($currentAddress->usage == 0) {
            $currentAddress->delete();
        }


        return $address;
    }
}
