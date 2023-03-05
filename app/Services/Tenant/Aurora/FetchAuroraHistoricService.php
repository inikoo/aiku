<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 18:21:19 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraHistoricService extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData['service'] = $this->parseService($this->auroraModelData->{'Product ID'});


        $deleted_at = $this->parseDate($this->auroraModelData->{'Product History Valid To'});

        $status = 0;
        if (DB::connection('aurora')->table('Product Dimension')->where('Product Current Key', '=', $this->auroraModelData->{'Product Key'})->exists()) {
            $status     = 1;
            $deleted_at = null;
        }


        $this->parsedData['historic_service'] = [
            'code'       => $this->auroraModelData->{'Product History Code'},
            'name'       => $this->auroraModelData->{'Product History Name'},
            'price'      => $this->auroraModelData->{'Product History Price'},
            'status'     => $status,
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
