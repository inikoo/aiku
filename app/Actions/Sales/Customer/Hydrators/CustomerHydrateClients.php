<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:46:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Sales\Customer;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateClients implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Customer $customer): void
    {
        $stats = [
            'number_clients'        => $customer->clients->count(),
            'number_active_clients' => $customer->clients->where('status', true)->count(),
        ];
        $customer->stats->update($stats);
    }

    public function getJobUniqueId(Customer $customer): int
    {
        return $customer->id;
    }
}
