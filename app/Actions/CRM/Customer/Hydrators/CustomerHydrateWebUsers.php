<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\CRM\Customer;
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
