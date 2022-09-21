<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 29 Jan 2022 01:05:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\CRM\CustomerClient;

use App\Models\CRM\CustomerClient;
use App\Actions\WithActionUpdate;

class UpdateCustomerClient
{
    use WithActionUpdate;

    public function handle(CustomerClient $customerClient, array $modelData): CustomerClient
    {
        return $this->update($customerClient, $modelData, ['data']);
    }
}
