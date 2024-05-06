<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 00:52:25 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDelivery;

use App\Actions\HydrateModel;
use App\Actions\Procurement\SupplierDelivery\Hydrators\SupplierDeliveriesHydrateItems;
use App\Models\Procurement\SupplierDelivery;
use Illuminate\Support\Collection;

class HydrateSupplierDeliveries extends HydrateModel
{
    public string $commandSignature = 'hydrate:supplier-deliveries {organisations?*} {--i|id=}';

    public function handle(SupplierDelivery $supplierDelivery): void
    {
        SupplierDeliveriesHydrateItems::run($supplierDelivery);
    }

    protected function getModel(string $slug): SupplierDelivery
    {
        return SupplierDelivery::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return SupplierDelivery::get();
    }
}
