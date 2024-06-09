<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Actions\Transfers\Aurora\FetchAuroraWarehouseAreas;
use App\Actions\Transfers\Aurora\FetchAuroraWarehouses;
use App\Transfers\Aurora\FetchAurora;
use Illuminate\Support\Facades\DB;

class FetchAuroraDeletedLocation extends FetchAurora
{
    protected function parseModel(): void
    {
        $parent = null;

        $deleted_at = $this->auroraModelData->{'Location Deleted Date'};
        if (!$deleted_at) {
            return;
        }

        if (is_numeric($this->auroraModelData->{'Location Deleted Warehouse Area Key'})) {
            $parent = FetchAuroraWarehouseAreas::run($this->organisationSource, $this->auroraModelData->{'Location Deleted Warehouse Area Key'});
        }
        if (!$parent) {
            $parent = FetchAuroraWarehouses::run($this->organisationSource, $this->auroraModelData->{'Location Deleted Warehouse Key'});
        }

        $this->parsedData['parent']   = $parent;
        $this->parsedData['location'] = [
            'code'       => $this->auroraModelData->{'Location Deleted Code'},
            'source_id'  => $this->organisation->id.':'.$this->auroraModelData->{'Location Deleted Key'},
            'deleted_at' => $deleted_at
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Location Deleted Dimension')
            ->where('Location Deleted Key', $id)->first();
    }
}
