<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 09 May 2023 17:02:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder\Traits;

use App\Actions\ProcurementToDelete\Supplier\Hydrators\SupplierHydratePurchaseOrders;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydratePurchaseOrders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Models\Procurement\PurchaseOrder;

trait HasHydrators
{
    public function purchaseOrderHydrate(PurchaseOrder $purchaseOrder): void
    {
        $parent = $purchaseOrder->provider;

        if (class_basename($parent) == 'Supplier') {
            SupplierHydratePurchaseOrders::dispatch($parent);
        } else {
            AgentHydratePurchaseOrders::dispatch($parent);
        }

        OrganisationHydrateProcurement::dispatch($purchaseOrder->organisation);
    }
}
