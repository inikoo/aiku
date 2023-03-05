<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer;

use App\Actions\WithActionUpdate;
use App\Models\Sales\Customer;

class UpdateCustomer
{
    use WithActionUpdate;

    public function handle(Customer $customer, array $modelData): Customer
    {
        return $this->update($customer, $modelData, ['data', 'tax_number_data']);
    }
}
