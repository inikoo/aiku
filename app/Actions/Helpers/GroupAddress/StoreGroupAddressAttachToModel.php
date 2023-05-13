<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 14 May 2023 01:51:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\GroupAddress;

use App\Models\Helpers\GroupAddress;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreGroupAddressAttachToModel
{
    use AsAction;

    public function handle(
        Agent|Supplier $model,
        array $addressData,
        array $scopeData
    ): GroupAddress {
        $address = StoreGroupAddress::run($addressData);
        $model->addresses()->attach($address->id, $scopeData);

        return $address;
    }
}
