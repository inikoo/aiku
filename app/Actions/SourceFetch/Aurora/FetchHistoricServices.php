<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 18:17:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Models\Market\HistoricOuter;
use App\Services\Organisation\SourceOrganisationService;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchHistoricServices
{
    use AsAction;


    public function handle(SourceOrganisationService $organisationSource, int $source_id): ?HistoricOuter
    {
        if ($historicProductData = $organisationSource->fetchHistoricService($source_id)) {
            if ($historicProduct = HistoricOuter::withTrashed()->where('source_id', $historicProductData['historic_service']['source_id'])
                ->first()) {
                $historicService = UpdateHistoricProduct::run(
                    historicService: $historicService,
                    modelData:       $historicServiceData['historic_service'],
                );
            } else {
                $historicService = StoreHistoricProduct::run(
                    service:   $historicServiceData['service'],
                    modelData: $historicServiceData['historic_service']
                );
            }


            return $historicService;
        }


        return null;
    }
}
