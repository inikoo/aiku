<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Aug 2024 22:13:05 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Search;

use App\Models\CRM\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Customer $customer): void
    {
        if ($customer->trashed()) {
            $customer->universalSearch()->delete();
            return;
        }

        $customer->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $customer->group_id,
                'organisation_id'   => $customer->organisation_id,
                'organisation_slug' => $customer->organisation->slug,
                'shop_id'           => $customer->shop_id,
                'shop_slug'         => $customer->shop->slug,
                'sections'          => ['crm'],
                'haystack_tier_1'   => trim($customer->email.' '.$customer->contact_name.' '.$customer->company_name),
            ]
        );
    }

}
