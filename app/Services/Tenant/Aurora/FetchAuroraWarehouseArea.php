<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:15:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Tenant\Aurora;

use App\Actions\SourceFetch\Aurora\FetchWarehouses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraWarehouseArea extends FetchAurora
{


    protected function parseModel(): void
    {
        $this->parsedData['warehouse'] = FetchWarehouses::run($this->tenantSource, $this->auroraModelData->{'Warehouse Area Warehouse Key'});

        $this->parsedData['warehouse_area'] = [
            'name'      => $this->auroraModelData->{'Warehouse Area Name'} ?? 'Name not set',
            'code'      => $this->auroraModelData->{'Warehouse Area Code'},
            'source_id' => $this->auroraModelData->{'Warehouse Area Key'},
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Warehouse Area Dimension')
            ->where('Warehouse Area Key', $id)->first();
    }

}
