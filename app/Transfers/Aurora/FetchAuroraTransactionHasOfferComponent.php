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
        $transaction = $this->parseTransaction($this->organisation->id.':'.$this->auroraModelData->{'Order Transaction Fact Key'});

        if (!$transaction) {
            //print "Transaction not found\n";
            //dd($this->auroraModelData);
            return;
        }

        if ($this->auroraModelData->{'Deal Component Key'} == '0') {
            $offerComponent = $order->shop->offerComponents()->where('is_discretionary', true)->first();
        } else {
            $offerComponent = $this->parseOfferComponent($this->organisation->id.':'.$this->auroraModelData->{'Deal Component Key'});
        }

        if (!$offerComponent) {
            print 'No offer component found for '.$this->auroraModelData->{'Deal Component Key'}."\n";
            print_r($this->auroraModelData);

            return;
        }


        $this->parsedData['transaction']     = $transaction;
        $this->parsedData['offer_component'] = $offerComponent;


        $fractionDiscount = $this->auroraModelData->{'Fraction Discount'};
        if ($fractionDiscount > 1) {
            $fractionDiscount = 1;
        }


        $this->parsedData['transaction_has_offer_component'] = [
            'source_id'             => $this->organisation->id.':'.$this->auroraModelData->{'Order Transaction Deal Key'},
            'offer_component_id'    => $offerComponent->id,
            'discounted_amount'     => $this->auroraModelData->{'Amount Discount'},
            'discounted_percentage' => $fractionDiscount,
            'info'                  => $this->auroraModelData->{'Deal Info'},
            'is_pinned'             => $this->auroraModelData->{'Order Transaction Deal Pinned'} == 'Yes',
            'fetched_at'            => now(),
            'last_fetched_at'       => now(),
        ];

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
