<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierProduct;

use App\Actions\Procurement\SupplierProduct\Hydrators\SupplierProductHydrateUniversalSearch;
use App\Models\SupplyChain\SupplierProduct;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateSupplierProductsUniversalSearch
{
    use AsAction;
    public string $commandSignature = 'supplier-products:search';

    public function handle(SupplierProduct $supplierProduct): void
    {
        SupplierProductHydrateUniversalSearch::run($supplierProduct);
    }

    public function asCommand(Command $command): int
    {
        $command->withProgressBar(SupplierProduct::withTrashed()->get(), function ($supplierProduct) {
            if ($supplierProduct) {
                $this->handle($supplierProduct);
            }
        });

        return 0;
    }


}
