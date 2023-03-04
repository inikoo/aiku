<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 05 Mar 2023 01:28:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions;


use App\Models\Central\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

trait WithTenantsOption{
    protected function getTenants($command): Collection|array
    {
        $tenants = Arr::wrap($command->option('tenant'));

        return  Tenant::query()
            ->when(!blank($tenants), function ($query) use ($tenants) {
                $query->whereIn('slug',$tenants);
            })->get();
    }

}
