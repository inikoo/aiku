<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:47:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Sales\Customer;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateWebUsers implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Customer $customer): void
    {
        $stats = [
            'number_web_users'        => $customer->webUsers->count(),
            'number_active_web_users' => $customer->webUsers->where('status', true)->count(),
        ];
        $customer->stats->update($stats);
    }

    public function getJobUniqueId(Customer $customer): int
    {
        return $customer->id;
    }
}
