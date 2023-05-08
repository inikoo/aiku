<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 May 2023 15:26:45 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant;

use App\Actions\Procurement\SupplierProduct\Hydrators\SupplierProductHydrateUniversalSearch;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Models\Procurement\SupplierProduct;
use App\Models\Tenancy\Tenant;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachSupplierProduct
{
    use AsAction;

    public function handle(Tenant $tenant, SupplierProduct $supplierProduct, array $pivotData = []): Tenant
    {
        return $tenant->execute(function (Tenant $tenant) use ($supplierProduct, $pivotData) {
            $tenant->supplierProducts()->attach($supplierProduct, $pivotData);
            TenantHydrateProcurement::dispatch($tenant);
            SupplierProductHydrateUniversalSearch::dispatch($supplierProduct);

            return $tenant;
        });
    }

}
