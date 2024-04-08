<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 15:11:16 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Market\HistoricOuter;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Market\HistoricOuter;

class UpdateHistoricOuter
{
    use WithActionUpdate;

    public function handle(HistoricOuter $historicProduct, array $modelData): HistoricOuter
    {
        return $this->update($historicProduct, $modelData);
    }
}
