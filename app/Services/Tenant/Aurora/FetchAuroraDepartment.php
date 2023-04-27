<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:00:42 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraDepartment extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData['shop'] = $this->parseShop($this->auroraModelData->{'Product Category Store Key'});

        $this->parsedData['department'] = [
            'code'       => $this->auroraModelData->{'Category Code'},
            'name'       => $this->auroraModelData->{'Category Label'},
            'state'      => match ($this->auroraModelData->{'Product Category Status'}) {
                'In Process' => 'in-process',
                default      => strtolower($this->auroraModelData->{'Product Category Status'})
            },
            'created_at'            => $this->parseDate($this->auroraModelData->{'Product Category Valid From'}),
            'source_department_id'  => $this->auroraModelData->{'Category Key'},
        ];
    }

    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Category Dimension')
            ->leftJoin('Product Category Dimension', 'Product Category Key', 'Category Key')
            ->where('Category Key', $id)->first();
    }
}
