<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 02:08:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraPaymentServiceProvider extends FetchAurora
{


    protected function parseModel(): void
    {
        $data = [
            'service-code' => Str::lower($this->auroraModelData->{'Payment Service Provider Block'}),
        ];

        $this->parsedData['paymentServiceProvider'] = [


            'code' => $this->auroraModelData->{'Payment Service Provider Code'},
            'type' => Str::lower($this->auroraModelData->{'Payment Service Provider Type'}),
            'data' => $data,

            'source_id' => $this->auroraModelData->{'Payment Service Provider Key'},


        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Payment Service Provider Dimension')
            ->where('Payment Service Provider Key', $id)->first();
    }

}
