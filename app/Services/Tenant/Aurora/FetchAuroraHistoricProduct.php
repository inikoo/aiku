<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:34:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;


use App\Actions\SourceFetch\Aurora\FetchProducts;
use Illuminate\Support\Facades\DB;

class FetchAuroraHistoricProduct extends FetchAurora
{


    protected function parseModel(): void
    {
        $this->parsedData['product'] = FetchProducts::run($this->tenantSource, $this->auroraModelData->{'Product ID'});


        $deleted_at = $this->parseDate($this->auroraModelData->{'Product History Valid To'});

        $status = 0;
        if (DB::connection('aurora')->table('Product Dimension')->where('Product Current Key', '=', $this->auroraModelData->{'Product Key'})->exists()) {
            $status     = 1;
            $deleted_at = null;
        }


        $units = $this->auroraModelData->{'Product History Units Per Case'};
        if ($units == 0) {
            $units = 1;
        }

        $this->parsedData['historic_product'] = [
            'code' => $this->auroraModelData->{'Product History Code'},
            'name' => $this->auroraModelData->{'Product History Name'},

            'price' => $this->auroraModelData->{'Product History Price'} / $units,
            'outer' => $units,

            'status' => $status,

            'created_at' => $this->parseDate($this->auroraModelData->{'Product History Valid From'}),
            'deleted_at' => $deleted_at,
            'source_id'  => $this->auroraModelData->{'Product Key'}
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product History Dimension')
            ->where('Product Key', $id)->first();
    }

}
