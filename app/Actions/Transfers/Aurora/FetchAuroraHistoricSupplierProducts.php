<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 14:28:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\SupplyChain\HistoricSupplierProduct\StoreHistoricSupplierProduct;
use App\Actions\SupplyChain\HistoricSupplierProduct\UpdateHistoricSupplierProduct;
use App\Models\SupplyChain\HistoricSupplierProduct;
use App\Transfers\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchAuroraHistoricSupplierProducts
{
    use AsAction;


    public function handle(SourceOrganisationService $organisationSource, int $source_id): ?HistoricSupplierProduct
    {
        if ($historicProductData = $organisationSource->fetchHistoricSupplierProduct($source_id)) {
            if ($historicProduct = HistoricSupplierProduct::withTrashed()->where('source_id', $historicProductData['historic_supplier_product']['source_id'])
                ->first()) {
                $historicProduct = UpdateHistoricSupplierProduct::make()->action(
                    historicSupplierProduct: $historicProduct,
                    modelData: $historicProductData['historic_supplier_product'],
                    strict: false
                );
            } else {
                $historicProduct = StoreHistoricSupplierProduct::make()->action(
                    supplierProduct: $historicProductData['supplier_product'],
                    modelData: $historicProductData['historic_supplier_product'],
                    strict: false
                );
            }
            $sourceData = explode(':', $historicProduct->source_id);

            DB::connection('aurora')->table('Product History Dimension')
                ->where('Product Key', $sourceData[1])
                ->update(['aiku_id' => $historicProduct->id]);

            return $historicProduct;
        }


        return null;
    }
}
