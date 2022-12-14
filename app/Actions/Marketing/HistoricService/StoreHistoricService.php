<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Dec 2022 02:22:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\HistoricService;

use App\Models\Marketing\HistoricService;
use App\Models\Marketing\Service;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreHistoricService
{
    use AsAction;

    public function handle(Service $service, array $modelData = []): HistoricService
    {
        $historicServiceData = [
            'code'       => Arr::get($modelData, 'code', $service->code),
            'name'       => Arr::get($modelData, 'name', $service->name),
            'price'      => Arr::get($modelData, 'price', $service->price),
            'source_id'  => Arr::get($modelData, 'source_id'),


        ];
        if (Arr::get($modelData, 'created_at')) {
            $historicServiceData['created_at'] = Arr::get($modelData, 'created_at');
        } else {
            $historicServiceData['created_at'] = $service->created_at;
        }
        if (Arr::get($modelData, 'deleted_at')) {
            $historicServiceData['deleted_at'] = Arr::get($modelData, 'deleted_at');
        }
        if (Arr::exists($modelData, 'status')) {
            $historicServiceData['status'] = Arr::exists($modelData, 'status');
        } else {
            $historicServiceData['status'] = true;
        }

        /** @var HistoricService $historicService */
        $historicService = $service->historicRecords()->create($historicServiceData);
        $historicService->stats()->create();

        return $historicService;
    }
}
