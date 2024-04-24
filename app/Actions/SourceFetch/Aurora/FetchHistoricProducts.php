<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:23:37 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Market\HistoricOuterable\StoreHistoricOuterable;
use App\Actions\Market\HistoricOuterable\UpdateHistoricOuterable;
use App\Models\Market\HistoricOuterable;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchHistoricProducts
{
    use AsAction;


    public function handle(SourceOrganisationService $organisationSource, int $source_id): ?HistoricOuterable
    {
        if ($historicProductData = $organisationSource->fetchHistoricProduct($source_id)) {

            if ($historicProduct = HistoricOuterable::withTrashed()->where('source_id', $historicProductData['historic_outerable']['source_id'])
                ->first()) {
                $historicProduct = UpdateHistoricOuterable::run(
                    historicProduct: $historicProduct,
                    modelData:       $historicProductData['historic_outerable'],
                );
            } else {
                $historicProduct = StoreHistoricOuterable::run(
                    outerable:   $historicProductData['product'],
                    modelData: $historicProductData['historic_outerable']
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
