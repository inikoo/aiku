<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:36:28 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier\Hydrators;

use App\Actions\HydrateModel;
use App\Models\Procurement\SupplierDelivery;
use Illuminate\Support\Collection;

class HydrateSupplierDeliveries extends HydrateModel
{
    public string $commandSignature = 'hydrate:supplierDeliveries {organisations?*} {--i|id=}';

    public function handle(SupplierDelivery $supplierDelivery): void
    {
        SupplierDeliveriesHydrateItems::run($supplierDelivery);
    }

    protected function getModel(int $id): SupplierDelivery
    {
        return SupplierDelivery::findOrFail($id);
    }

    protected function getAllModels(): Collection
    {
        return SupplierDelivery::get();
    }
}
