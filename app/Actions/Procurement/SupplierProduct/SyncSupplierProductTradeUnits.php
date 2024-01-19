<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 May 2023 15:26:45 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierProduct;

use App\Models\SupplyChain\SupplierProduct;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncSupplierProductTradeUnits
{
    use AsAction;

    public function handle(SupplierProduct $supplierProduct, array $tradeUnitsData): SupplierProduct
    {
        $supplierProduct->tradeUnits()->sync($tradeUnitsData);


        return $supplierProduct;
    }
}
