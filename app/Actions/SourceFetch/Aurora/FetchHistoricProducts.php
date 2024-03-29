<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:23:37 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Market\HistoricProduct\StoreHistoricProduct;
use App\Actions\Market\HistoricProduct\UpdateHistoricProduct;
use App\Models\Market\HistoricProduct;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchHistoricProducts
{
    use AsAction;


    public function handle(SourceOrganisationService $organisationSource, int $source_id): ?HistoricProduct
    {
        if ($historicProductData = $organisationSource->fetchHistoricProduct($source_id)) {
            if ($historicProduct = HistoricProduct::withTrashed()->where('source_id', $historicProductData['historic_product']['source_id'])
                ->first()) {
                $historicProduct = UpdateHistoricProduct::run(
                    historicProduct: $historicProduct,
                    modelData:       $historicProductData['historic_product'],
                );
            } else {
                $historicProduct = StoreHistoricProduct::run(
                    product:   $historicProductData['product'],
                    modelData: $historicProductData['historic_product']
                );
            }
            $sourceData = explode(':', $historicProduct->source_id);

            DB::connection('aurora')->table('Product History Dimension')
                ->where('Product Key', $sourceData[1])
                ->update(['aiku_id'=>$historicProduct->id]);

            return $historicProduct;
        }


        return null;
    }
}
