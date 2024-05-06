<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 00:50:45 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier;

use App\Actions\HydrateModel;

use App\Actions\SupplyChain\Supplier\Hydrators\SupplierHydratePurchaseOrders;
use App\Actions\SupplyChain\Supplier\Hydrators\SupplierHydrateSupplierDeliveries;
use App\Actions\SupplyChain\Supplier\Hydrators\SupplierHydrateSupplierProducts;
use App\Models\SupplyChain\Supplier;
use Illuminate\Support\Collection;

class HydrateSupplier extends HydrateModel
{
    public string $commandSignature = 'hydrate:supplier {organisations?*} {--i|id=} ';

    public function handle(Supplier $supplier): void
    {
        SupplierHydrateSupplierProducts::run($supplier);
        SupplierHydratePurchaseOrders::run($supplier);
        SupplierHydrateSupplierDeliveries::run($supplier);
    }


    protected function getModel(string $slug): Supplier
    {
        return Supplier::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Supplier::withTrashed()->get();
    }
}
