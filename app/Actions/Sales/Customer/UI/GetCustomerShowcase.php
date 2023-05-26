<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer\UI;

use App\Models\Sales\Customer;
use Lorisleiva\Actions\Concerns\AsObject;

class GetCustomerShowcase
{
    use AsObject;

    public function handle(Customer $customer): array
    {
        return [
            []
        ];
    }
}
