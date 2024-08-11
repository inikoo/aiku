<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 00:50:45 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier;

use App\Actions\HydrateModel;

use App\Actions\SupplyChain\Supplier\Hydrators\SupplierHydratePurchaseOrders;
use App\Actions\SupplyChain\Supplier\Hydrators\SupplierHydrateStockDeliveries;
use App\Actions\SupplyChain\Supplier\Hydrators\SupplierHydrateSupplierProducts;
use App\Models\SupplyChain\Supplier;
use Illuminate\Console\Command;

class HydrateSupplier extends HydrateModel
{
    public string $commandSignature = 'hydrate:suppliers';

    public function handle(Supplier $supplier): void
    {
        SupplierHydrateSupplierProducts::run($supplier);
        SupplierHydratePurchaseOrders::run($supplier);
        SupplierHydrateStockDeliveries::run($supplier);
    }

    public function asCommand(Command $command): int
    {

        $command->withProgressBar(Supplier::all(), function ($supplier) {
            $this->handle($supplier);

        });
        $command->info("");

        return 0;
    }



}
