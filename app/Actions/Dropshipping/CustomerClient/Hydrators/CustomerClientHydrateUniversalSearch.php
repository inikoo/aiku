<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Dropshipping\CustomerClient\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Dropshipping\CustomerClient;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerClientHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(CustomerClient $customerClient): void
    {
        $customerClient->universalSearch()->create(
            [
                'primary_term'   => $customerClient->name.' '.$customerClient->email,
                'secondary_term' => $customerClient->contact_name.' '.$customerClient->company_name
            ]
        );
    }

}
