<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 18:14:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Service;

use App\Actions\WithActionUpdate;
use App\Models\Marketing\Service;

class UpdateService
{
    use WithActionUpdate;

    public function handle(Service $service, array $modelData, bool $skipHistoric=false): Service
    {
        $service= $this->update($service, $modelData, ['data', 'settings']);
        if(!$skipHistoric and $service->wasChanged(
                ['price', 'code','name']
            )){
            //todo create HistoricService and update current_historic_service_id if
        }

        return $service;
    }
}
