<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 22:05:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraPaymentAccount extends FetchAurora
{


    protected function parseModel(): void
    {

        $data=[];

        $this->parsedData['paymentServiceProvider'] = $this->parsePaymentServiceProvider($this->auroraModelData->{'Payment Account Service Provider Key'});

        $this->parsedData['paymentAccount'] = [


            'code' => $this->auroraModelData->{'Payment Account Code'},
            'name' => $this->auroraModelData->{'Payment Account Name'},

            'data' => $data,

            'source_id' => $this->auroraModelData->{'Payment Account Key'},


        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Payment Account Dimension')
            ->where('Payment Account Key', $id)->first();
    }

}
