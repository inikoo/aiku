<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:27:33 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Department;

use App\Models\Central\Tenant;
use App\Models\Marketing\Department;
use App\Models\Marketing\Shop;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreDepartment
{
    use AsAction;

    public function handle(Shop $shop, array $modelData): Department
    {
        /** @var Department $department */
        $department = $shop->departments()->create($modelData);

        $department->stats()->create();
        $department->salesStats()->create([
                                           'scope' => 'sales'
                                       ]);
        /** @var Tenant $tenant */
        $tenant=app('currentTenant');
        if ($department->shop->currency_id != $tenant->currency_id) {
            $department->salesStats()->create([
                                               'scope' => 'sales-tenant-currency'
                                           ]);
        }

        return $department;
    }
}
