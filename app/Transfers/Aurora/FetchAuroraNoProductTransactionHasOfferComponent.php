<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 18:51:19 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Models\Ordering\Order;
use Illuminate\Support\Facades\DB;

class FetchAuroraNoProductTransactionHasOfferComponent extends FetchAurora
{
    protected function parseNoProductTransactionHasOfferComponent(Order $order): void
    {
        $transaction = $this->parseTransactionNoProduct($this->organisation->id.':'.$this->auroraModelData->{'Order No Product Transaction Fact Key'});

        if (!$transaction) {
            return;
        }

        if ($this->auroraModelData->{'Deal Component Key'} == '0') {
            $offerComponent = $order->shop->offerComponents()->where('is_discretionary', true)->first();
        } else {
            $offerComponent = $this->parseOfferComponent($this->organisation->id.':'.$this->auroraModelData->{'Deal Component Key'});
        }

        if (!$offerComponent) {
            print 'No offer component found (in no-product)  for '.$this->auroraModelData->{'Deal Component Key'}."\n";
            print_r($this->auroraModelData);
            return;
        }

        if ($offerComponent->shop_id != $order->shop_id) {
            print 'Offer Component '.$offerComponent->id.' does not belong to the same shop as the order '.$order->id."\n";
            dd($this->auroraModelData);
        }


        $this->parsedData['transaction']     = $transaction;
        $this->parsedData['offer_component'] = $offerComponent;

        $this->parsedData['transaction_has_offer_component'] = [
            'source_alt_id'         => $this->organisation->id.':'.$this->auroraModelData->{'Order No Product Transaction Deal Key'},
            'offer_component_id'    => $offerComponent->id,
            'discounted_amount'     => $this->auroraModelData->{'Amount Discount'},
            'discounted_percentage' => $this->auroraModelData->{'Fraction Discount'},
            'info'                  => $this->auroraModelData->{'Deal Info'},
            'is_pinned'             => $this->auroraModelData->{'Order No Product Transaction Deal Pinned'} == 'Yes',
            'precursor'             => $this->auroraModelData->{'Order No Product Transaction Deal Source'},
            'fetched_at'            => now(),
            'last_fetched_at'       => now(),
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order No Product Transaction Deal Bridge')
            ->where('Order No Product Transaction Deal Key', $id)->first();
    }

    public function fetchNoProductTransactionHasOfferComponent(int $id, Order $order): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        if ($this->auroraModelData) {
            $this->parseNoProductTransactionHasOfferComponent($order);
        }

        return $this->parsedData;
    }
}