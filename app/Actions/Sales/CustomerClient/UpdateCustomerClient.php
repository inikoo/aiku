<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\CustomerClient;

use App\Actions\WithActionUpdate;
use App\Models\Sales\CustomerClient;

class UpdateCustomerClient
{
    use WithActionUpdate;

    public function handle(CustomerClient $customerClient, array $modelData): CustomerClient
    {
        return $this->update($customerClient, $modelData, ['data']);
    }
}
