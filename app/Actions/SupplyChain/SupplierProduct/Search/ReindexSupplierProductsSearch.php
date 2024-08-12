<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Aug 2024 14:47:12 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierProduct\Search;

use App\Models\SupplyChain\SupplierProduct;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class ReindexSupplierProductsSearch
{
    use AsAction;
    public string $commandSignature = 'supplier-products:search';

    public function handle(SupplierProduct $supplierProduct): void
    {
        SupplierProductRecordSearch::run($supplierProduct);
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
