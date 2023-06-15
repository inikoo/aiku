<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Sales\Customer\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Sales\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Customer $customer): void
    {
        $customer->universalSearch()->create(
            [
                'primary_term'   => $customer->name.' '.$customer->email,
                'secondary_term' => $customer->contact_name.' '.$customer->company_name
            ]
        );
    }

}
