<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateFulfilmentCustomers
{
    use AsAction;


    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_customers_with_stored_items' => $organisation->fulfilmentCustomers()->where('number_stored_items', '>', 0)->count(),
            'number_customers_with_pallets'      => $organisation->fulfilmentCustomers()->where('number_pallets', '>', 0)->count(),
        ];

        $organisation->fulfilmentStats()->update($stats);
    }
}
