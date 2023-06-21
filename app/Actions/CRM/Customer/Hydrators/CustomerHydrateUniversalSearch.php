<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\CRM\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Customer $customer): void
    {
        $customer->universalSearch()->create(
            [
                'section' => 'CRM',
                'route'   => json_encode([
                    'name'      => 'crm.shops.show.customers.show',
                    'arguments' => [
                        $customer->shop->slug,
                        $customer->slug
                    ]
                ]),
                'icon'           => 'fa-cash-register',
                'primary_term'   => $customer->name.' '.$customer->email,
                'secondary_term' => $customer->contact_name.' '.$customer->company_name
            ]
        );
    }

}
