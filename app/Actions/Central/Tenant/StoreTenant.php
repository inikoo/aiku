<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 14:51:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant;


use App\Models\Central\Tenant;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreTenant
{
    use AsAction;

    public function handle(array $modelData): Tenant
    {
        $tenant = Tenant::create($modelData);
        $tenant->stats()->create();
        $tenant->inventoryStats()->create();

        return $tenant;
    }
}
