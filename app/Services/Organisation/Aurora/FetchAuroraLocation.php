<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 14:07:59 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Actions\SourceUpserts\Aurora\Single\UpsertWarehouseAreaFromSource;
use App\Actions\SourceUpserts\Aurora\Single\UpsertWarehouseFromSource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraLocation extends FetchAurora
{


    protected function parseModel(): void
    {


        $parent=null;

        if(is_numeric($this->auroraModelData->{'Location Warehouse Area Key'})){
            $parent= UpsertWarehouseAreaFromSource::run($this->organisationSource, $this->auroraModelData->{'Location Warehouse Area Key'});
        }
        if(!$parent){
            $parent= UpsertWarehouseFromSource::run($this->organisationSource, $this->auroraModelData->{'Location Warehouse Key'});
        }

        $this->parsedData['parent'] =$parent;
        $this->parsedData['location'] = [
            'code'                   => Str::snake(strtolower($this->auroraModelData->{'Location Code'}), '-'),
            'organisation_source_id' => $this->auroraModelData->{'Location Key'},
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Location Dimension')
            ->where('Location Key', $id)->first();
    }

}
