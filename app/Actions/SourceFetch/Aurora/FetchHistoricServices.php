<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 18:17:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Marketing\HistoricService\StoreHistoricService;
use App\Actions\Marketing\HistoricService\UpdateHistoricService;
use App\Models\Marketing\HistoricService;
use App\Services\Tenant\SourceTenantService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class FetchHistoricServices
{
    use AsAction;


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $source_id): ?HistoricService
    {
        if ($historicServiceData = $tenantSource->fetchHistoricService($source_id)) {


            if ($historicService = HistoricService::withTrashed()->where('source_id', $historicServiceData['historic_service']['source_id'])
                ->first()) {
                $historicService = UpdateHistoricService::run(
                    historicService: $historicService,
                    modelData:       $historicServiceData['historic_service'],
                );
            } else {
                $historicService = StoreHistoricService::run(
                    service:   $historicServiceData['service'],
                    modelData: $historicServiceData['historic_service']
                );
            }


            return $historicService;
        }


        return null;
    }


}
