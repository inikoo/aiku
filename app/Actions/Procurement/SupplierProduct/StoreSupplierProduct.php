<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 18:00:17 Malaysia Time, Bali Airport
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierProduct;

use App\Actions\Procurement\HistoricSupplierProduct\StoreHistoricSupplierProduct;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreSupplierProduct
{
    use AsAction;

    public function handle(Supplier $supplier, array $modelData, bool $skipHistoric = false): SupplierProduct
    {
        /** @var SupplierProduct $supplierProduct */

        $supplierProduct = $supplier->products()->create($modelData);

        $supplierProduct->stats()->create();

        if (!$skipHistoric) {
            $historicProduct = StoreHistoricSupplierProduct::run($supplierProduct);
            $supplierProduct->update(
                [
                    'current_historic_product_id' => $historicProduct->id
                ]
            );
        }


        return $supplierProduct;
    }
}
