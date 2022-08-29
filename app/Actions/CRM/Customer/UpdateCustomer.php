<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 14 Oct 2021 01:12:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\CRM\Customer;

use App\Models\Utils\ActionResult;
use App\Actions\WithUpdate;
use App\Models\CRM\Customer;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCustomer
{
    use AsAction;
    use WithUpdate;

    public function handle(
        Customer $customer,
        array $customerData,
    ): ActionResult {
        $res = new ActionResult();



        $customer->update( Arr::except($customerData, ['data','tax_number_data']));
        $customer->update($this->extractJson($customerData));


        $res->changes = $customer->getChanges();

        $res->model    = $customer;
        $res->model_id = $customer->id;
        $res->status   = $res->changes ? 'updated' : 'unchanged';

        return $res;
    }
}
