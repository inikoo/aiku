<?php
/*
 * author Arya Permana - Kirin
 * created on 18-11-2024-11h-14m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Discounts\ModelHasOfferComponent;
;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;
use App\Models\Discounts\ModelHasDiscount;
use App\Models\Discounts\ModelHasOfferComponent;
use App\Models\Discounts\OfferComponent;
use App\Models\Ordering\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreModelHasOfferComponent extends OrgAction
{
    use WithNoStrictRules;

    public function handle(InvoiceTransaction|Transaction $model, OfferComponent $offerComponent, array $modelData): ModelHasOfferComponent
    {
        data_set($modelData, 'offer_campaign_id', $offerComponent->offer_campaign_id);
        data_set($modelData, 'offer_id', $offerComponent->offer_id);
        data_set($modelData, 'offer_component_id', $offerComponent->id);

        $modelHasDiscount = $model->offerComponents()->create($modelData);
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
