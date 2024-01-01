<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 14:07:59 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Actions\SourceFetch\Aurora\FetchWarehouses;
use App\Actions\SourceFetch\Aurora\FetchWarehouseAreas;
use Illuminate\Support\Facades\DB;

class FetchAuroraLocation extends FetchAurora
{
    protected function parseModel(): void
    {
        $parent = null;

        if (is_numeric($this->auroraModelData->{'Location Warehouse Area Key'})) {
            $parent = FetchWarehouseAreas::run($this->organisationSource, $this->auroraModelData->{'Location Warehouse Area Key'});
        }
        if (!$parent) {
            $parent = FetchWarehouses::run($this->organisationSource, $this->auroraModelData->{'Location Warehouse Key'});
        }

        $this->parsedData['parent']   = $parent;
        $this->parsedData['location'] = [
            'code'                     => $this->auroraModelData->{'Location Code'},
            'source_id'                => $this->organisation->id.':'.$this->auroraModelData->{'Location Key'},
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Location Dimension')
            ->where('Location Key', $id)->first();
    }
}
