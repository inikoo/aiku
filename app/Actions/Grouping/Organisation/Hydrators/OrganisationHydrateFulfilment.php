<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Grouping\Organisation\Hydrators;

use App\Models\CRM\Customer;
use App\Models\Fulfilment\StoredItem;
use App\Models\Sales\Order;
use App\Models\Grouping\Organisation;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateFulfilment implements ShouldBeUnique
{
    use AsAction;
    use HasOrganisationHydrate;

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
