<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\UI;

use App\Http\Resources\CRM\CustomerClientResource;
use App\Http\Resources\CRM\CustomersResource;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\CustomerClient;
use Lorisleiva\Actions\Concerns\AsObject;

class GetCustomerClientShowcase
{
    use AsObject;

    public function handle(CustomerClient $customerClient): array
    {
        return [

               'data'=> CustomerClientResource::make($customerClient)->getArray()


        ];
    }
}
