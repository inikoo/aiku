<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 22:05:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Enums\Accounting\Payment\PaymentStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraPayment extends FetchAurora
{
    protected function parseModel(): void
    {
        $data = [];

        if ($this->auroraModelData->{'Payment Currency Exchange Rate'} and
            $this->auroraModelData->{'Payment Currency Exchange Rate'} != 1) {
            $data['exchange'] = $this->auroraModelData->{'Payment Currency Exchange Rate'};
        }


        $this->parsedData['paymentAccount'] = $this->parsePaymentAccount($this->auroraModelData->{'Payment Account Key'});
        $this->parsedData['customer']       = $this->parseCustomer($this->auroraModelData->{'Payment Customer Key'});



        $state  = Str::lower($this->auroraModelData->{'Payment Transaction Status'});
        $status = PaymentStatusEnum::IN_PROCESS;
        switch ($this->auroraModelData->{'Payment Transaction Status'}) {
            case 'Pending':
                $state = PaymentStateEnum::IN_PROCESS;
                break;
            case 'Completed':
                $status = PaymentStatusEnum::SUCCESS;

                break;

            case 'Cancelled':
            case 'Error':
            case 'Declined':
                $status = PaymentStatusEnum::FAIL;
        }


        $this->parsedData['payment'] = [

            'payment_account_id' => $this->parsedData['paymentAccount']->id,
            'reference'          => $this->auroraModelData->{'Payment Transaction ID'},
            'amount'             => $this->auroraModelData->{'Payment Transaction Amount'},
            'tc_amount'          => $this->auroraModelData->{'Payment Transaction Amount'} * $this->auroraModelData->{'Payment Currency Exchange Rate'},
            'data'               => $data,
            'currency_id'        => $this->parseCurrencyID($this->auroraModelData->{'Payment Currency Code'}),

            'source_id'    => $this->organisation->id.':'.$this->auroraModelData->{'Payment Key'},
            'date'         => $this->parseDate($this->auroraModelData->{'Payment Last Updated Date'}),
            'created_at'   => $this->parseDate($this->auroraModelData->{'Payment Created Date'}),
            'completed_at' => $this->parseDate($this->auroraModelData->{'Payment Completed Date'}),

            'cancelled_at' => $this->parseDate($this->auroraModelData->{'Payment Cancelled Date'}),

            'state'  => $state,
            'status' => $status,


        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Payment Dimension')
            ->where('Payment Key', $id)->first();
    }
}
