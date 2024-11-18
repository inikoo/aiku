<?php
/*
 * author Arya Permana - Kirin
 * created on 18-11-2024-11h-14m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Discounts\ModelHasOfferComponent;

;

use App\Actions\Accounting\Invoice\Hydrators\InvoiceHydrateOffers;
use App\Actions\Discounts\Offer\Hydrators\OfferHydrateInvoices;
use App\Actions\Discounts\OfferCampaign\Hydrators\OfferCampaignHydrateInvoices;
use App\Actions\Discounts\OfferCampaign\Hydrators\OfferCampaignHydrateOrders;
use App\Actions\Discounts\OfferComponent\Hydrators\OfferComponentHydrateInvoices;
use App\Actions\Discounts\OfferComponent\Hydrators\OfferComponentHydrateOrders;
use App\Actions\Discounts\OfferComponent\Hydrators\OfferHydrateOrders;
use App\Actions\Ordering\Order\Hydrators\OrderHydrateOffers;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Accounting\InvoiceTransaction;
use App\Models\Discounts\ModelHasOfferComponent;
use App\Models\Discounts\OfferComponent;
use App\Models\Ordering\Transaction;

class StoreModelHasOfferComponent extends OrgAction
{
    use WithNoStrictRules;

    public function handle(InvoiceTransaction|Transaction $model, OfferComponent $offerComponent, array $modelData): ModelHasOfferComponent
    {
        data_set($modelData, 'offer_campaign_id', $offerComponent->offer_campaign_id);
        data_set($modelData, 'offer_id', $offerComponent->offer_id);
        data_set($modelData, 'offer_component_id', $offerComponent->id);

        $modelHasDiscount = $model->offerComponents()->create($modelData);

        if ($model instanceof InvoiceTransaction) {
            OfferComponentHydrateInvoices::dispatch($modelHasDiscount->offerComponent);
            OfferHydrateInvoices::dispatch($modelHasDiscount->offer);
            OfferCampaignHydrateInvoices::dispatch($modelHasDiscount->offerCampaign);
            InvoiceHydrateOffers::dispatch($modelHasDiscount->model->invoice);
        } elseif ($model instanceof Transaction) {
            OfferComponentHydrateOrders::dispatch($modelHasDiscount->offerComponent);
            OfferHydrateOrders::dispatch($modelHasDiscount->offer);
            OfferCampaignHydrateOrders::dispatch($modelHasDiscount->offerCampaign);
            OrderHydrateOffers::dispatch($modelHasDiscount->model->order);
        }

        return $modelHasDiscount;
    }

    public function rules(): array
    {
        $rules = [
        ];

        if (!$this->strict) {

            $rules = $this->noStrictStoreRules($rules);

        }

        return $rules;
    }

    public function action(InvoiceTransaction|Transaction $model, OfferComponent $offerComponent, array $modelData, int $hydratorsDelay = 0, bool $strict = true): ModelHasOfferComponent
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($offerComponent->shop, $modelData);

        return $this->handle($model, $offerComponent, $modelData);
    }
}
