<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 10 May 2023 14:06:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierPurchaseOrder\Traits;

use App\Actions\ProcurementToDelete\Supplier\Hydrators\HydrateSupplierPurchaseOrders;
use App\Actions\ProcurementToDelete\Supplier\Hydrators\SupplierHydratePurchaseOrders;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydratePurchaseOrders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Models\Procurement\PurchaseOrder;

trait HasHydrators
{
    public function getHydrators(PurchaseOrder $supplierPurchaseOrder): void
    {
        $parent = $supplierPurchaseOrder->provider;

        if(class_basename($parent) == 'Supplier') {
            SupplierHydratePurchaseOrders::dispatch($parent);
        } else {
            AgentHydratePurchaseOrders::dispatch($parent);
        }

        HydrateSupplierPurchaseOrders::dispatch($supplierPurchaseOrder);

        OrganisationHydrateProcurement::dispatch(app('currentTenant'));
    }
}
