<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Dec 2022 02:27:34 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\HistoricService;

use App\Actions\WithActionUpdate;
use App\Models\Marketing\HistoricService;

class UpdateHistoricService
{
    use WithActionUpdate;

    public function handle(HistoricService $historicService, array $modelData): HistoricService
    {
        return $this->update($historicService, $modelData);
    }
}
