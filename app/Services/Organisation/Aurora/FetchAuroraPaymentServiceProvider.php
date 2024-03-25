<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 02:08:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderTypeEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraPaymentServiceProvider extends FetchAurora
{
    protected function parseModel(): void
    {
        if ($this->auroraModelData->{'Payment Service Provider Type'} == 'Account') {
            return;
        }


        $type = match (Str::lower($this->auroraModelData->{'Payment Service Provider Type'})) {
            'eps'   => PaymentServiceProviderTypeEnum::ELECTRONIC_PAYMENT_SERVICE->value,
            'ebep'  => PaymentServiceProviderTypeEnum::ELECTRONIC_BANKING_E_PAYMENT->value,
            'cash'  => PaymentServiceProviderTypeEnum::CASH->value,
            'bank'  => PaymentServiceProviderTypeEnum::BANK->value,
            'bpl'   => PaymentServiceProviderTypeEnum::BUY_NOW_PAY_LATER->value,
            'cond'  => PaymentServiceProviderTypeEnum::CASH_ON_DELIVERY->value,
            default => 'error'
        };


        if ($type === 'error') {
            dd(Str::lower($this->auroraModelData->{'Payment Service Provider Type'}));
        }


        $code = strtolower($this->organisation->slug.'-'.$this->auroraModelData->{'Payment Service Provider Code'});



        $this->parsedData['paymentServiceProvider'] = [
            'name'      => $this->auroraModelData->{'Payment Service Provider Name'},
            'code'      => $code,
            'type'      => $type,
            'source_id' => $this->organisation->id.':'.$this->auroraModelData->{'Payment Service Provider Key'},


        ];

        $createdDateData = DB::connection('aurora')
            ->table('Payment Account Dimension')
            ->select('Payment Account From')
            ->where('Payment Account Service Provider Key', $this->auroraModelData->{'Payment Service Provider Key'})
            ->orderBy('Payment Account From')->first();

        if ($createdDateData and $this->parseDate($createdDateData->{'Payment Account From'})) {
            $this->parsedData['paymentServiceProvider']['created_at'] = $this->parseDate($createdDateData->{'Payment Account From'});
        } else {
            $createdDateData = DB::connection('aurora')->table('Payment Dimension')
                ->select('Payment Created Date as date')
                ->where('Payment Service Provider Key', $this->auroraModelData->{'Payment Service Provider Key'})
                ->orderBy('Payment Created Date')->first();

            if ($createdDateData and $this->parseDate($createdDateData->{'date'})) {
                $this->parsedData['paymentServiceProvider']['created_at'] = $this->parseDate($createdDateData->{'date'});
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
