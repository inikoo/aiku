<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jul 2023 12:57:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Hydrators;

use App\Models\CRM\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateStoredItems
{
    use AsAction;


    public function handle(Customer $customer): void
    {
        if(!$customer->is_fulfilment) {
            return;
        }
        $stats = [
            'number_stored_items'        => $customer->storedItems->count(),
        ];
        $customer->fulfilmentStats()->update($stats);
    }


}
