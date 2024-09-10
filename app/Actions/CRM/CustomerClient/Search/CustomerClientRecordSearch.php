<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 22:50:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\CustomerClient\Search;

use App\Models\Dropshipping\CustomerClient;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerClientRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(CustomerClient $customerClient): void
    {

        if($customerClient->trashed()) {
            $customerClient->universalSearch()->delete();
            return;
        }

        $customerClient->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $customerClient->group_id,
                'organisation_id'   => $customerClient->organisation_id,
                'organisation_slug' => $customerClient->organisation->slug,
                'shop_id'           => $customerClient->shop_id,
                'shop_slug'         => $customerClient->shop->slug,
                'customer_id'       => $customerClient->customer_id,
                'customer_slug'     => $customerClient->customer->slug,
                'sections'          => ['crm'],
                'haystack_tier_1'   => join(
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
