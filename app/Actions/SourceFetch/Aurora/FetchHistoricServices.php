<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 18:17:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Market\HistoricOuter\StoreHistoricOuter;
use App\Actions\Market\HistoricOuter\UpdateHistoricOuter;
use App\Models\Market\HistoricOuter;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchHistoricServices
{
    use AsAction;


    public function handle(SourceOrganisationService $organisationSource, int $source_id): ?HistoricOuter
    {
        if ($historicProductData = $organisationSource->fetchHistoricService($source_id)) {
            if ($historicProduct = HistoricOuter::withTrashed()->where('source_id', $historicProductData['historic_service']['source_id'])
                ->first()) {
                $historicProduct = UpdateHistoricOuter::run(
                    historicProduct: $historicProduct,
                    modelData:       $historicProductData['historic_service'],
                );
            } else {
                $historicProduct = StoreHistoricOuter::run(
                    product:   $historicProductData['service'],
                    modelData: $historicProductData['historic_service']
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
