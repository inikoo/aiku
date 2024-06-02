<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:23:37 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\HistoricAsset\UpdateHistoricAsset;
use App\Models\Catalogue\HistoricAsset;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchHistoricProducts
{
    use AsAction;


    public function handle(SourceOrganisationService $organisationSource, int $source_id): ?HistoricAsset
    {
        if ($historicProductData = $organisationSource->fetchHistoricProduct($source_id)) {

            if ($historicProduct = HistoricAsset::withTrashed()->where('source_id', $historicProductData['historic_asset']['source_id'])
                ->first()) {
                $historicProduct = UpdateHistoricAsset::run(
                    historicProduct: $historicProduct,
                    modelData:       $historicProductData['historic_asset'],
                );
            } else {
                $historicProduct = StoreHistoricAsset::run(
                    outerable:   $historicProductData['product'],
                    modelData: $historicProductData['historic_asset']
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
