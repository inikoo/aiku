<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 26 Feb 2024 19:57:44 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer\UI;

use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use Lorisleiva\Actions\Concerns\AsObject;

class GetFulfilmentCustomerShowcase
{
    use AsObject;

    public function handle(FulfilmentCustomer $fulfilmentCustomer): array
    {
        return [

            'customer'            => CustomerResource::make($fulfilmentCustomer->customer)->getArray(),
            'fulfilment_customer' => FulfilmentCustomerResource::make($fulfilmentCustomer)->getArray(),


        ];
    }
}
