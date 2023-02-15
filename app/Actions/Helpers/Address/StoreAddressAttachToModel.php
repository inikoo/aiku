<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 09 Feb 2023 17:13:57 Malaysia Time, Ubud, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Address;

use App\Models\Dropshipping\CustomerClient;
use App\Models\Helpers\Address;
use App\Models\Marketing\Shop;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Sales\Customer;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreAddressAttachToModel
{

    use AsAction;

    public function handle(
        Shop|Customer|CustomerClient|Agent|Supplier $model,
        array $addressData,
        array $scopeData
    ): Address {
        $address = StoreAddress::run($addressData);
        $model->addresses()->attach($address->id, $scopeData);

        return $address;
    }
}
