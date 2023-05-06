<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 May 2023 11:40:23 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant;

use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateUniversalSearch;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Models\Procurement\Supplier;
use App\Models\Tenancy\Tenant;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachSupplier
{
    use AsAction;

    public function handle(Tenant $tenant, Supplier $supplier, array $pivotData = []): Tenant
    {
        return $tenant->execute(function (Tenant $tenant) use ($supplier, $pivotData) {
            $tenant->suppliers()->attach($supplier, $pivotData);
            TenantHydrateProcurement::dispatch($tenant);
            SupplierHydrateUniversalSearch::dispatch($supplier);

            return $tenant;
        });
    }

}
