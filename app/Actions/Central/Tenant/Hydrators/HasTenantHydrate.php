<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 02:29:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant\Hydrators;

use App\Models\Central\Tenant;

trait HasTenantHydrate
{
    public function getJobUniqueId(Tenant $tenant): string
    {
        return $tenant->id;
    }

    public function getJobTags(): array
    {
        /** @var Tenant $tenant */
        $tenant=app('currentTenant');
        return ['central','tenant:'.$tenant->code];
    }


}
