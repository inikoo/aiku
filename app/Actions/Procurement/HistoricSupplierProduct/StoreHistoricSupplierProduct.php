<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 18:17:38 Malaysia Time, Bali Airport
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\HistoricSupplierProduct;

use App\Models\Procurement\HistoricSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreHistoricSupplierProduct
{
    use AsAction;

    public function handle(SupplierProduct $supplierProduct, array $modelData = []): HistoricSupplierProduct
    {
        $historicSupplierProductData = [
            'code'       => Arr::get($modelData, 'code', $supplierProduct->code),
            'name'       => Arr::get($modelData, 'name', $supplierProduct->name),
            'cost'       => Arr::get($modelData, 'cost', $supplierProduct->cost),
//            'units'      => Arr::get($modelData, 'units', $supplierProduct->units),
            'source_id'  => Arr::get($modelData, 'source_id'),


        ];
        if (Arr::get($modelData, 'created_at')) {
            $historicSupplierProductData['created_at'] = Arr::get($modelData, 'created_at');
        } else {
            $historicSupplierProductData['created_at'] = $supplierProduct->created_at;
        }
        if (Arr::get($modelData, 'deleted_at')) {
            $historicSupplierProductData['deleted_at'] = Arr::get($modelData, 'deleted_at');
        }
        if (Arr::exists($modelData, 'status')) {
            $historicSupplierProductData['status'] = Arr::exists($modelData, 'status');
        } else {
            $historicSupplierProductData['status'] = true;
        }

        /** @var HistoricSupplierProduct $historicSupplierProduct */
        $historicSupplierProduct = $supplierProduct->historicAssets()->create($historicSupplierProductData);
        $historicSupplierProduct->stats()->create();

        return $historicSupplierProduct;
    }
}
