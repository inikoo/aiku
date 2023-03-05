<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 22:05:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraPaymentAccount extends FetchAurora
{
    protected function parseModel(): void
    {
        $data = [
            'service-code' => Str::lower($this->auroraModelData->{'Payment Account Block'})
        ];

        $this->parsedData['paymentServiceProvider'] = $this->parsePaymentServiceProvider($this->auroraModelData->{'Payment Account Service Provider Key'});

        $this->parsedData['paymentAccount'] = [


            'code'      => $this->auroraModelData->{'Payment Account Code'},
            'name'      => $this->auroraModelData->{'Payment Account Name'},
            'data'      => $data,
            'source_id' => $this->auroraModelData->{'Payment Account Key'},


        ];

        if ($this->auroraModelData->{'Payment Account Block'} == 'Accounts') {
            if ($createdDateData = DB::connection('aurora')->table('Payment Account Store Bridge')
                ->leftJoin('Store Dimension', 'Store Key', 'Payment Account Store Store Key')
                ->select('Store Code')
                ->where('Payment Account Store Payment Account Key', $this->auroraModelData->{'Payment Account Key'})
                ->first()) {
                $this->parsedData['paymentAccount']['slug'] = 'account-'.Str::lower($createdDateData->{'Store Code'});
            }
        }

        if ($this->parseDate($this->auroraModelData->{'Payment Account From'})) {
            $this->parsedData['paymentAccount']['created_at'] = $this->parseDate($this->auroraModelData->{'Payment Account From'});
        } else {
            $createdDateData = DB::connection('aurora')->table('Payment Dimension')
                ->select('Payment Created Date as date')
                ->where('Payment Account Key', $this->auroraModelData->{'Payment Account Key'})
                ->orderBy('Payment Created Date')->first();

            if ($createdDateData and $this->parseDate($createdDateData->{'date'})) {
                $this->parsedData['paymentServiceProvider']['created_at'] = $this->parseDate($createdDateData->{'date'});
            }
        }
    }


    protected function fetchData(
        $id
    ): object|null {
        return DB::connection('aurora')
            ->table('Payment Account Dimension')
            ->where('Payment Account Key', $id)->first();
    }
}
