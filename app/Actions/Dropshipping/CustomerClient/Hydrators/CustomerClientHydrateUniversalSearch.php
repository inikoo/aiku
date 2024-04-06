<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Dropshipping\CustomerClient\Hydrators;

use App\Models\Dropshipping\CustomerClient;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerClientHydrateUniversalSearch
{
    use AsAction;


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
