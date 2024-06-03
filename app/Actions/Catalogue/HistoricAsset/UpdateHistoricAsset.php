<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 15:11:16 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\HistoricAsset;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Catalogue\HistoricAsset;

class UpdateHistoricAsset
{
    use WithActionUpdate;

    public function handle(HistoricAsset $historicAsset, array $modelData): HistoricAsset
    {
        return $this->update($historicAsset, $modelData);
    }
}
