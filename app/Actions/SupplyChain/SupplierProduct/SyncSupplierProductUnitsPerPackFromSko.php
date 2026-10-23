<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierProduct;

use App\Models\SupplyChain\SupplierProduct;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncSupplierProductUnitsPerPackFromSko
{
    use AsAction;

    public function handle(SupplierProduct $supplierProduct): SupplierProduct
    {
        $skoPackedIn = $supplierProduct->skoPackedIn();
        if ($skoPackedIn === null || $skoPackedIn === $supplierProduct->units_per_pack) {
            return $supplierProduct;
        }

        return UpdateSupplierProduct::make()->action($supplierProduct, ['units_per_pack' => $skoPackedIn], strict: false);
    }
}
