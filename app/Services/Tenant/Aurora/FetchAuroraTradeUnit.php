<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 03:04:22 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraTradeUnit extends FetchAurora
{
    use WithAuroraImages;

    protected function parseModel(): void
    {

        $this->parsedData['trade_unit'] = [
            'name'      => $this->auroraModelData->{'Part Recommended Product Unit Name'},
            'code'      => $this->auroraModelData->{'Part Reference'},
            'source_id' => $this->auroraModelData->{'Part SKU'},
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Part Dimension')
            ->where('Part SKU', $id)->first();
    }
}
