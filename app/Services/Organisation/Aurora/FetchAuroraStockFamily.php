<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 12:37:55 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraStockFamily extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData['stock_family'] = [
            'code'                     => $this->auroraModelData->{'Category Code'},
            'name'                     => $this->auroraModelData->{'Category Label'},
            'source_id'                => $this->organisation->id.':'.$this->auroraModelData->{'Category Key'},
            'state'                    => match ($this->auroraModelData->{'Part Category Status'}) {
                'InUse'         => 'active',
                'Discontinuing' => 'discontinuing',
                'NotInUse'      => 'discontinued',
                default         => 'in-process',
            }
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Category Dimension')
            ->leftJoin('Part Category Dimension', 'Part Category Key', 'Category Key')
            ->where('Category Key', $id)->first();
    }
}
