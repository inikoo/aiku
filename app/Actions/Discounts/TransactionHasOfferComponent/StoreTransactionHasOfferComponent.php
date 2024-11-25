<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 16:01:17 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Discounts\TransactionHasOfferComponent;

use App\Actions\Discounts\Offer\Hydrators\OfferHydrateOrders;
use App\Actions\Discounts\OfferCampaign\Hydrators\OfferCampaignHydrateOrders;
use App\Actions\Discounts\OfferComponent\Hydrators\OfferComponentHydrateOrders;
use App\Actions\Ordering\Order\Hydrators\OrderHydrateOffers;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Discounts\OfferComponent;
use App\Models\Discounts\TransactionHasOfferComponent;
use App\Models\Ordering\Transaction;

class StoreTransactionHasOfferComponent extends OrgAction
{
    use WithNoStrictRules;

    public function handle(Transaction $transaction, OfferComponent $offerComponent, array $modelData): TransactionHasOfferComponent
    {
        data_set($modelData, 'offer_campaign_id', $offerComponent->offer_campaign_id);
        data_set($modelData, 'offer_id', $offerComponent->offer_id);
        data_set($modelData, 'offer_component_id', $offerComponent->id);

        data_set($modelData, 'model_type', $transaction->model_type);
        data_set($modelData, 'model_id', $transaction->model_id);

        data_set($modelData, 'order_id', $transaction->order_id);


        /** @var TransactionHasOfferComponent $transactionHasOfferComponent */
        $transactionHasOfferComponent = $transaction->offerComponents()->create($modelData);

        OfferComponentHydrateOrders::dispatch($transactionHasOfferComponent->offerComponent);
        OfferHydrateOrders::dispatch($transactionHasOfferComponent->offer);
        OfferCampaignHydrateOrders::dispatch($transactionHasOfferComponent->offerCampaign);
        OrderHydrateOffers::dispatch($transaction->order);


        return $transactionHasOfferComponent;
    }

    public function rules(): array
    {
        $rules = [
            'is_pinned'             => ['sometimes', 'boolean'],
            'info'                  => ['sometimes', 'nullable', 'string', 'max:10000'],
            'data'                  => ['sometimes', 'nullable', 'array'],
            'discounted_amount'     => ['required', 'nullable', 'numeric'],
            'discounted_percentage' => ['required', 'nullable', 'numeric', 'min:0', 'max:1'],
            'precursor'             => ['sometimes', 'nullable', 'string'],
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    public function action(Transaction $transaction, OfferComponent $offerComponent, array $modelData, int $hydratorsDelay = 0, bool $strict = true): TransactionHasOfferComponent
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($offerComponent->shop, $modelData);

        return $this->handle($transaction, $offerComponent, $modelData);
    }
}
