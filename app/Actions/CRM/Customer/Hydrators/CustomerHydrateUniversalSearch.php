<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Hydrators;

use App\Actions\Traits\WithOrganisationJob;
use App\Models\CRM\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateUniversalSearch
{
    use AsAction;
    use WithOrganisationJob;

    public function handle(Customer $customer): void
    {
        $customer->universalSearch()->updateOrCreate(
            [],
            [
                'section' => 'crm',
                'title'   => trim($customer->email.' '.$customer->contact_name.' '.$customer->company_name),
            ]
        );
    }

}
