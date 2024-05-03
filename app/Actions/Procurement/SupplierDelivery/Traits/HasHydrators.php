<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 10 May 2023 14:06:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDelivery\Traits;

use App\Actions\ProcurementToDelete\Supplier\Hydrators\HydrateSupplierDeliveries;
use App\Actions\ProcurementToDelete\Supplier\Hydrators\SupplierHydrateSupplierDeliveries;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydrateSupplierDeliveries;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Models\Procurement\SupplierDelivery;

trait HasHydrators
{
    public function getHydrators(SupplierDelivery $supplierDelivery): void
    {
        $parent = $supplierDelivery->provider;

        if(class_basename($parent) == 'Supplier') {
            SupplierHydrateSupplierDeliveries::dispatch($parent);
        } else {
            AgentHydrateSupplierDeliveries::dispatch($parent);
        }

        HydrateSupplierDeliveries::dispatch($supplierDelivery);

        OrganisationHydrateProcurement::dispatch($supplierDelivery->organisation);
    }
}
