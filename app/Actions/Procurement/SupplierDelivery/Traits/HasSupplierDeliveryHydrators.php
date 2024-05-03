<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 10 May 2023 14:06:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDelivery\Traits;

use App\Actions\SupplyChain\Agent\Hydrators\AgentHydrateSupplierDeliveries;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgPartner;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\SupplierDelivery;

trait HasSupplierDeliveryHydrators
{
    public function runHydrators(SupplierDelivery $supplierDelivery): void
    {
        /** @var OrgSupplier|OrgAgent|OrgPartner $parent */
        $parent = $supplierDelivery->parent;

        if(class_basename($parent) == 'OrgSupplier') {
            //SupplierHydrateSupplierDeliveries::dispatch($parent);
        } elseif(class_basename($parent) == 'OrgAgent') {
            AgentHydrateSupplierDeliveries::dispatch($parent->agent);
        }


    }
}
