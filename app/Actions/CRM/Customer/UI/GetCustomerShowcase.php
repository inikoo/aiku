<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\UI;

use App\Models\CRM\Customer;
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
