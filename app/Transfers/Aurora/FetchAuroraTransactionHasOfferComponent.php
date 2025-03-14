<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 18:26:23 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Models\Ordering\Order;
use Illuminate\Support\Facades\DB;

class FetchAuroraTransactionHasOfferComponent extends FetchAurora
{
    protected function parseTransactionHasOfferComponent(Order $order): void
    {

        if ($this->auroraModelData->{'Amount Discount'} < 0) {
            return;
        }

        $transaction = $this->parseTransaction($this->organisation->id.':'.$this->auroraModelData->{'Order Transaction Fact Key'});

        if (!$transaction) {
            return;
        }

        if ($this->auroraModelData->{'Deal Component Key'} == '0') {
            $offerComponent = $order->shop->offerComponents()->where('is_discretionary', true)->first();
        } else {
            $offerComponent = $this->parseOfferComponent($this->organisation->id.':'.$this->auroraModelData->{'Deal Component Key'});
        }

        $data = [];
        if (!$offerComponent) {
            $data           = [
                'fetch_error'      => true,
                'fetch_error_data' => [
                    'aurora_deal_component_key' => $this->auroraModelData->{'Deal Component Key'},
                ]
            ];
            $offerComponent = $order->shop->offerComponents()->where('is_discretionary', true)->first();
        }


        $this->parsedData['transaction']     = $transaction;
        $this->parsedData['offer_component'] = $offerComponent;


        $fractionDiscount = $this->auroraModelData->{'Fraction Discount'};
        if ($fractionDiscount > 1) {
            $fractionDiscount = 1;
        }
        if ($fractionDiscount <= 0) {
            unset($fractionDiscount);
        }

        $this->parsedData['transaction_has_offer_component'] = [
            'source_id'          => $this->organisation->id.':'.$this->auroraModelData->{'Order Transaction Deal Key'},
            'offer_component_id' => $offerComponent->id,
            'discounted_amount'  => $this->auroraModelData->{'Amount Discount'},
            'info'               => $this->auroraModelData->{'Deal Info'},
            'is_pinned'          => $this->auroraModelData->{'Order Transaction Deal Pinned'} == 'Yes',
            'fetched_at'         => now(),
            'last_fetched_at'    => now(),
            'data'               => $data,
        ];

        if (isset($fractionDiscount)) {
            $this->parsedData['transaction_has_offer_component']['discounted_amount'] = $fractionDiscount;
        }

        if (!($this->auroraModelData->{'Order Transaction Deal Metadata'} == '' or $this->auroraModelData->{'Order Transaction Deal Metadata'} == '{}')) {
            $this->parsedData['transaction_has_offer_component']['data'] = json_decode($this->auroraModelData->{'Order Transaction Deal Metadata'}, true);
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order Transaction Deal Bridge')
            ->where('Order Transaction Deal Key', $id)->first();
    }

    public function fetchTransactionHasOfferComponent(int $id, Order $order): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        if ($this->auroraModelData) {
            $this->parseTransactionHasOfferComponent($order);
        }

        return $this->parsedData;
    }
}
