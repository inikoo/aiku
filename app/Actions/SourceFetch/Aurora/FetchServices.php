<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 17:28:37 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Marketing\Service\StoreService;
use App\Actions\Marketing\Service\UpdateService;
use App\Models\Marketing\HistoricService;
use App\Models\Marketing\Service;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchServices extends FetchAction
{
    public string $commandSignature = 'fetch:services {tenants?*} {--s|source_id=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Service
    {
        if ($serviceData = $tenantSource->fetchService($tenantSourceId)) {
            if ($service = Service::where('source_id', $serviceData['service']['source_id'])
                ->first()) {
                $service = UpdateService::run(
                    service:      $service,
                    modelData:    $serviceData['service'],
                    skipHistoric: true
                );
            } else {
                $service = StoreService::run(
                    shop:         $serviceData['shop'],
                    modelData:    $serviceData['service'],
                    skipHistoric: true
                );
            }


            $historicService = HistoricService::where('source_id', $serviceData['historic_service_source_id'])->first();

            if (!$historicService) {
                $historicService = FetchHistoricServices::run($tenantSource, $serviceData['historic_service_source_id']);
            }

            $service->update(
                [
                    'current_historic_service_id' => $historicService->id
                ]
            );

            return $service;
        }


        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product Type', 'Service')
            ->select('Product ID as source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Product Dimension')->where('Product Type', 'Service')->count();
    }
}
