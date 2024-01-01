<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:15:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraWarehouseArea extends FetchAurora
{
    protected function parseModel(): void
    {
        $code=$this->auroraModelData->{'Warehouse Area Code'};
        $code=preg_replace('/\s*warehouse$/i', '', $code);



        $this->parsedData['warehouse']      = $this->parseWarehouse($this->auroraModelData->{'Warehouse Area Warehouse Key'});
        $this->parsedData['warehouse_area'] = [
            'name'                     => $this->auroraModelData->{'Warehouse Area Name'} ?? 'Name not set',
            'code'                     => Str::kebab($code),
            'source_id'                => $this->organisation->id.':'.$this->auroraModelData->{'Warehouse Area Key'},
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Warehouse Area Dimension')
            ->where('Warehouse Area Key', $id)->first();
    }
}
