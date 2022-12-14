<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 18:02:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Service;

use App\Actions\Marketing\HistoricService\StoreHistoricService;
use App\Models\Central\Tenant;
use App\Models\Marketing\Service;
use App\Models\Marketing\Shop;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreService
{
    use AsAction;

    public function handle(Shop $shop, array $modelData, bool $skipHistoric = false): Service
    {
        /** @var Service $service */
        $service = $shop->services()->create($modelData);
        $service->stats()->create();
        if (!$skipHistoric) {
            $historicService = StoreHistoricService::run($service);
            $service->update(
                [
                    'current_historic_service_id' => $historicService->id
                ]
            );
        }
        $service->salesStats()->create([
                                           'scope' => 'sales'
                                       ]);
        /** @var Tenant $tenant */
        $tenant = tenant();
        if ($service->shop->currency_id != $tenant->currency_id) {
            $service->salesStats()->create([
                                               'scope' => 'sales-tenant-currency'
                                           ]);
        }

        return $service;
    }
}
