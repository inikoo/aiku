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
use Illuminate\Database\Eloquent\Collection;

class HydrateSuppliers extends HydrateModel
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


        $count = Supplier::count();
        $bar   = $command->getOutput()->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();
        Supplier::chunk(1000, function (Collection $models) use ($bar) {
            foreach ($models as $model) {
                $this->handle($model);
                $bar->advance();
            }
        });
        $bar->finish();

        return 0;
    }



}
