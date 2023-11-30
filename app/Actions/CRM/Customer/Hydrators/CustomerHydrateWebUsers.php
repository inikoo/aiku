<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Hydrators;

use App\Models\CRM\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateWebUsers
{
    use AsAction;


    public function handle(Customer $customer): void
    {
        $stats = [
            'number_web_users'        => $customer->webUsers->count(),
            'number_active_web_users' => $customer->webUsers->where('status', true)->count(),
        ];
        $customer->stats->update($stats);
    }


}
