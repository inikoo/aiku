<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 15:11:16 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\HistoricOuterable;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Catalogue\HistoricOuterable;

class UpdateHistoricOuterable
{
    use WithActionUpdate;

    public function handle(HistoricOuterable $historicProduct, array $modelData): HistoricOuterable
    {
        return $this->update($historicProduct, $modelData);
    }
}
