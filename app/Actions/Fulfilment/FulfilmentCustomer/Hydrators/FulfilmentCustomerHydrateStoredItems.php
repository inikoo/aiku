<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 19:23:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer\Hydrators;

use App\Models\Fulfilment\FulfilmentCustomer;
use Lorisleiva\Actions\Concerns\AsAction;

class FulfilmentCustomerHydrateStoredItems
{
    use AsAction;


    public function handle(FulfilmentCustomer $fulfilmentCustomer): void
    {

        $stats = [
            'number_stored_items'        => $fulfilmentCustomer->storedItems()->count(),
        ];
        $fulfilmentCustomer->update($stats);
    }


}
