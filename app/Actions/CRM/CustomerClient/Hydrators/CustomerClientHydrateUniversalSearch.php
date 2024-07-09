<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 07 Jun 2024 11:21:47 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\CustomerClient\Hydrators;

use App\Models\Dropshipping\CustomerClient;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerClientHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(CustomerClient $customerClient): void
    {
        $customerClient->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $customerClient->group_id,
                'organisation_id'   => $customerClient->organisation_id,
                'organisation_slug' => $customerClient->organisation->slug,
                'shop_id'           => $customerClient->shop_id,
                'shop_slug'         => $customerClient->shop->slug,
                'customer_id'       => $customerClient->customer_id,
                 'customer_slug'    => $customerClient->customer->slug,
                'section'           => 'crm',

                'title' => join(
                    ' ',
                    array_unique([
                        $customerClient->name,
                        $customerClient->email,
                        $customerClient->contact_name,
                        $customerClient->company_name
                    ])
                ),
            ]
        );
    }

}
