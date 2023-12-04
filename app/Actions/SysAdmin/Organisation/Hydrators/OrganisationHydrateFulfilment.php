<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Models\CRM\Customer;
use App\Models\Fulfilment\StoredItem;
use App\Models\SysAdmin\Organisation;
use App\Models\OMS\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateFulfilment
{
    use AsAction;


    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_customers_with_stored_items' => Customer::count(),
            'number_customers_with_assets'       => Order::count(),
            'number_stored_items'                => StoredItem::count()
        ];

        $organisation->fulfilmentStats->update($stats);
    }
}
