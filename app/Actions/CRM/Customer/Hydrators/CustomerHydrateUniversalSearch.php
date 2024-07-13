<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Hydrators;

use App\Models\CRM\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Customer $customer): void
    {
        if($customer->trashed()) {
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
                'section'           => 'crm',
                'title'             => trim($customer->email.' '.$customer->contact_name.' '.$customer->company_name),
            ]
        );
    }

}
