<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 10 May 2023 14:06:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDelivery\Traits;

use App\Actions\Procurement\Agent\Hydrators\AgentHydrateSupplierDeliveries;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateSupplierDeliveries;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;

trait HasHydrators {
    public function getHydrators(Agent|Supplier $parent): void
    {
        if(class_basename($parent) == 'Supplier') {
            SupplierHydrateSupplierDeliveries::dispatch($parent);
        } else {
            AgentHydrateSupplierDeliveries::dispatch($parent);
        }

        TenantHydrateProcurement::dispatch(app('currentTenant'));
    }
}
