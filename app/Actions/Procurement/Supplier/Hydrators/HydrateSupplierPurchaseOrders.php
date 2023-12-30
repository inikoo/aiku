<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:36:28 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier\Hydrators;

use App\Actions\HydrateModel;
use App\Actions\Procurement\PurchaseOrder\Hydrators\PurchaseOrderHydrateItems;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Support\Collection;

class HydrateSupplierPurchaseOrders extends HydrateModel
{
    public string $commandSignature = 'hydrate:supplierPurchaseOrder {organisations?*} {--i|id=}';

    public function handle(PurchaseOrder $supplierDelivery): void
    {
        PurchaseOrderHydrateItems::run($supplierDelivery);
    }

    protected function getModel(int $id): PurchaseOrder
    {
        return PurchaseOrder::findOrFail($id);
    }

    protected function getAllModels(): Collection
    {
        return PurchaseOrder::get();
    }
}
