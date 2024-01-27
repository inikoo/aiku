<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\OrgAction;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Market\Shop;

class StoreFulfilmentCustomer extends OrgAction
{
    public function handle(Customer $customer, Shop $shop): FulfilmentCustomer
    {
        /** @var FulfilmentCustomer $customerFulfilment */
        $customerFulfilment = $customer->fulfilmentCustomer()->create([
            'fulfilment_id'   => $shop->fulfilment->id,
            'group_id'        => $customer->group_id,
            'organisation_id' => $customer->organisation_id,
        ]);

        return $customerFulfilment;
    }
}
