<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 14 Oct 2021 01:12:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\CRM\Customer;

use App\Actions\WithActionUpdate;
use App\Models\CRM\Customer;


class UpdateCustomer
{
    use WithActionUpdate;

    public function handle(Customer $customer, array $modelData): Customer
    {
        return $this->update($customer, $modelData, ['data', 'tax_number_data']);
    }
}
