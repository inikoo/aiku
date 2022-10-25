<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 09:35:18 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use App\Models\Marketing\Department;
use App\Models\Marketing\Shop;
use Illuminate\Support\Facades\DB;

class FetchAuroraFamily extends FetchAurora
{
    protected function parseModel(): void
    {
        $parent = (new Department())->firstWhere('source_id', $this->auroraModelData->{'Product Category Department Category Key'});
        if (is_null($parent)) {
            $parent = (new Shop())->firstWhere('source_id', $this->auroraModelData->{'Product Category Store Key'});
        }

        $this->parsedData['parent'] = $parent;

        $this->parsedData['family'] = [
            'code'       => $this->auroraModelData->{'Category Code'},
            'name'       => $this->auroraModelData->{'Category Label'},
            'state'      => match ($this->auroraModelData->{'Product Category Status'}) {
                'In Process' => 'in-process',
                default => strtolower($this->auroraModelData->{'Product Category Status'})
            },
            'created_at' => $this->parseDate($this->auroraModelData->{'Product Category Valid From'}),
            'source_id'  => $this->auroraModelData->{'Category Key'},
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
