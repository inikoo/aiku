<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant\Hydrators;

use App\Models\CRM\Customer;
use App\Models\Fulfilment\StoredItem;
use App\Models\Sales\Order;
use App\Models\Tenancy\Tenant;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class TenantHydrateFulfilment implements ShouldBeUnique
{
    use AsAction;
    use HasTenantHydrate;

    public function handle(Tenant $tenant): void
    {
        $stats = [
            'number_customers_with_stored_items' => Customer::count(),
            'number_customers_with_assets'       => Order::count(),
            'number_stored_items'                => StoredItem::count()
        ];

        $tenant->fulfilmentStats->update($stats);
    }
}
