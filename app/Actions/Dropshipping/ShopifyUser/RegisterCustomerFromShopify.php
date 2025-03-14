<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 11 Jul 2024 10:16:14 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\ShopifyUser;

use App\Actions\OrgAction;
use App\Actions\Retina\SysAdmin\RegisterRetinaFulfilmentCustomer;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dropshipping\ShopifyUser;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class RegisterCustomerFromShopify extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(ShopifyUser $shopifyUser, $modelData): void
    {
        /*        data_set($modelData, 'group_id', $customer->group_id);
                data_set($modelData, 'organisation_id', $customer->organisation_id);

                RegisterRetinaFulfilmentCustomer::run();*/
    }
}
