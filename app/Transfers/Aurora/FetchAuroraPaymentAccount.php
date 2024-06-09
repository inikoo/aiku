<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraPaymentAccount extends FetchAurora
{
    protected function parseModel(): void
    {
        if ($this->auroraModelData->{'Payment Account Block'} == 'Accounts' or $this->auroraModelData->{'Payment Account Block'} == 'Other') {
            return;
        }

        $type = match ($this->auroraModelData->{'Payment Account Block'}) {
            'Accounts'    => PaymentAccountTypeEnum::ACCOUNT->value,
            'Paypal'      => PaymentAccountTypeEnum::PAYPAL->value,
            'WP'          => PaymentAccountTypeEnum::WORLD_PAY->value,
            'Sofort'      => PaymentAccountTypeEnum::SOFORT->value,
            'BTree'       => PaymentAccountTypeEnum::BRAINTREE->value,
            'BTreePaypal' => PaymentAccountTypeEnum::BRAINTREE_PAYPAL->value,
            'Bank'        => PaymentAccountTypeEnum::BANK->value,
            'Cash'        => PaymentAccountTypeEnum::CASH->value,
            'Hokodo'      => PaymentAccountTypeEnum::HOKODO->value,
            'Checkout'    => PaymentAccountTypeEnum::CHECKOUT->value,
            'Pastpay'     => PaymentAccountTypeEnum::PASTPAY->value,
            'ConD'        => PaymentAccountTypeEnum::CASH_ON_DELIVERY->value,
            default       => 'unknown'
        };

        if ($type === 'unknown') {
            dd($this->auroraModelData->{'Payment Account Block'});
        }

        $this->parsedData['paymentServiceProvider'] = $this->parseOrgPaymentServiceProvider($this->organisation->id.':'.$this->auroraModelData->{'Payment Account Service Provider Key'});


        $code = $this->auroraModelData->{'Payment Account Code'};
        $code = str_replace('.', '-', $code);
        $code = str_replace('_', '-', $code);

        $code = strtolower($code);




        if ($this->organisation->slug == 'aw') {
            if ($code == 'bankeur') {
                $code = 'aw-bank-eur';
            }
            if ($code == 'bankgbp') {
                $code = 'aw-bank-gbp';
            }

            if ($code == 'bankpln') {
                $code = 'aw-bank-pln';
            }
        }
        if ($this->organisation->slug == 'sk') {
            if ($code == 'bankpln') {
                $code = 'sk-bank-pln';
            }
            if ($code == 'bankpln2') {
                $code = 'sk-bank-pln2';
            }
            if ($code == 'bankeur') {
                $code = 'sk-bank-eur';
            }

            if (in_array($code, ['btree', 'btree-pln', 'paypal', 'checkout', 'hokodo'])) {
                $code = 'sk-'.$code;
            }
        }

        if ($this->organisation->slug == 'es') {
            if (in_array($code, ['btree', 'paypal', 'checkout', 'hokodo'])) {
                $code = 'es-'.$code;
            }
        }

        if ($this->organisation->slug == 'aroma') {
            if ($code == 'bankgbp') {
                $code = 'aro-bank-gbp';
            }
            if (in_array($code, ['btree', 'paypal', 'checkout', 'hokodo', 'cash', 'paypal-btree'])) {
                $code = 'aro-'.$code;
            }
        }


        $this->parsedData['paymentAccount'] = [


            'code'      => $code,
            'name'      => $this->auroraModelData->{'Payment Account Name'},
            'type'      => $type,
            'source_id' => $this->organisation->id.':'.$this->auroraModelData->{'Payment Account Key'},


        ];

        /*
        if ($this->auroraModelData->{'Payment Account Block'} == 'Accounts') {
            if ($createdDateData = DB::connection('aurora')->table('Payment Account Store Bridge')
                ->leftJoin('Store Dimension', 'Store Key', 'Payment Account Store Store Key')
                ->select('Store Code')
                ->where('Payment Account Store Payment Account Key', $this->auroraModelData->{'Payment Account Key'})
                ->first()) {
                $this->parsedData['paymentAccount']['slug'] = 'account-'.Str::lower($createdDateData->{'Store Code'});
            }
        }
*/

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
