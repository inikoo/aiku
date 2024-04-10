<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 02:08:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use App\Models\Accounting\PaymentServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraPaymentServiceProvider extends FetchAurora
{
    protected function parseModel(): void
    {
        if ($this->auroraModelData->{'Payment Service Provider Type'} == 'Account') {
            return;
        }

        $code = match (Str::lower($this->auroraModelData->{'Payment Service Provider Block'})) {
            'braintree' => 'btree',

            default => Str::lower($this->auroraModelData->{'Payment Service Provider Block'})
        };

        $paymentServiceProvider=PaymentServiceProvider::where('group_id', $this->organisation->group_id)
            ->where('code', $code)->firstOrFail();

        $this->parsedData['paymentServiceProvider'] =$paymentServiceProvider;





        $this->parsedData['orgPaymentServiceProvider'] = [
            'code'      => $paymentServiceProvider->code.'-'.$this->organisation->code,
            'source_id' => $this->organisation->id.':'.$this->auroraModelData->{'Payment Service Provider Key'},
        ];

        $createdDateData = DB::connection('aurora')
            ->table('Payment Account Dimension')
            ->select('Payment Account From')
            ->where('Payment Account Service Provider Key', $this->auroraModelData->{'Payment Service Provider Key'})
            ->orderBy('Payment Account From')->first();

        if ($createdDateData and $this->parseDate($createdDateData->{'Payment Account From'})) {
            $this->parsedData['orgPaymentServiceProvider']['created_at'] = $this->parseDate($createdDateData->{'Payment Account From'});
        } else {
            $createdDateData = DB::connection('aurora')->table('Payment Dimension')
                ->select('Payment Created Date as date')
                ->where('Payment Service Provider Key', $this->auroraModelData->{'Payment Service Provider Key'})
                ->orderBy('Payment Created Date')->first();

            if ($createdDateData and $this->parseDate($createdDateData->{'date'})) {
                $this->parsedData['orgPaymentServiceProvider']['created_at'] = $this->parseDate($createdDateData->{'date'});
            }
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Payment Service Provider Dimension')
            ->where('Payment Service Provider Key', $id)->first();
    }
}
