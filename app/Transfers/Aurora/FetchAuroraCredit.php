<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Aug 2024 11:55:49 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Accounting\Invoice\CreditTransactionTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraCredit extends FetchAurora
{
    use WithAuroraImages;

    protected function parseModel(): void
    {
        $customer = $this->parseCustomer($this->organisation->id.':'.$this->auroraModelData->{'Credit Transaction Customer Key'});
        $payment  = $this->parsePayment($this->organisation->id.':'.$this->auroraModelData->{'Credit Transaction Payment Key'});

        $date = $this->parseDatetime($this->auroraModelData->{'Credit Transaction Date'});


        $type = match ($this->auroraModelData->{'Credit Transaction Type'}) {
            'TopUp'            => CreditTransactionTypeEnum::TOP_UP,
            'Payment'          => CreditTransactionTypeEnum::PAYMENT,
            'Adjust'           => CreditTransactionTypeEnum::ADJUST,
            'Cancel'           => CreditTransactionTypeEnum::CANCEL,
            'Return'           => CreditTransactionTypeEnum::RETURN,
            'PayReturn'        => CreditTransactionTypeEnum::PAY_RETURN,
            'AddFundsOther'    => CreditTransactionTypeEnum::ADD_FUNDS_OTHER,
            'Compensation'     => CreditTransactionTypeEnum::COMPENSATION,
            'TransferIn'       => CreditTransactionTypeEnum::TRANSFER_IN,
            'MoneyBack'        => CreditTransactionTypeEnum::MONEY_BACK,
            'TransferOut'      => CreditTransactionTypeEnum::TRANSFER_OUT,
            'RemoveFundsOther' => CreditTransactionTypeEnum::REMOVE_FUNDS_OTHER,
            default            => null
        };

        $this->parsedData['customer'] = $customer;

        $this->parsedData['credit'] =
            [
                'date'      => $date,
                'type'      => $type,
                'amount'    => $this->auroraModelData->{'Credit Transaction Amount'},
                'source_id' => $this->organisation->id.':'.$this->auroraModelData->{'Credit Transaction Key'},
            ];

        if ($payment) {
            $this->parsedData['credit']['payment_id'] = $payment->id;
        }


    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Credit Transaction Fact')
            ->where('Credit Transaction Key', $id)->first();
    }
}