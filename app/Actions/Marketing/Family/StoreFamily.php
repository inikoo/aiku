<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:53:45 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Family;

use App\Models\Central\Tenant;
use App\Models\Marketing\Department;
use App\Models\Marketing\Family;
use App\Models\Marketing\Shop;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreFamily
{
    use AsAction;

    public function handle(Shop|Department $parent, array $modelData): Family
    {
        if (class_basename($parent) == 'Department') {
            $modelData['shop_id'] = $parent->shop_id;

            $modelData['root_department_id'] = $parent->department_id ?? $parent->id;
        }

        /** @var Family $family */
        $family = $parent->families()->create($modelData);
        $family->stats()->create();
        $family->salesStats()->create([
                                          'scope' => 'sales'
                                      ]);
        /** @var Tenant $tenant */
        $tenant = app('currentTenant');
        if ($family->shop->currency_id != $tenant->currency_id) {
            $family->salesStats()->create([
                                              'scope' => 'sales-tenant-currency'
                                          ]);
        }

        return $family;
    }
}
