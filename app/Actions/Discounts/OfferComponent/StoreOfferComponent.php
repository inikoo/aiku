<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:04:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Discounts\OfferComponent;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithStoreOffer;
use App\Enums\Discounts\OfferComponent\OfferComponentStateEnum;
use App\Models\Discounts\Offer;
use App\Models\Discounts\OfferComponent;
use App\Rules\IUnique;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreOfferComponent extends OrgAction
{
    use WithNoStrictRules;
    use WithStoreOffer;


    /**
     * @throws \Throwable
     */
    public function handle(Offer $offer, $trigger, array $modelData): OfferComponent
    {
        $modelData = $this->prepareOfferData($offer, $trigger, $modelData);
        data_set($modelData, 'offer_campaign_id', $offer->offer_campaign_id);


        return DB::transaction(function () use ($offer, $modelData) {
            /** @var $offerComponent OfferComponent */
            $offerComponent = $offer->offerComponents()->create($modelData);
            $offerComponent->stats()->create();

            return $offerComponent;
        });
    }

    public function rules(): array
    {
        $rules = [
            'code'          => [
                'required',
                new IUnique(
                    table: 'offer_components',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                    ]
                ),

                'max:64',
                'alpha_dash'
            ],
            'data'          => ['sometimes', 'required'],
            'start_at'      => ['sometimes', 'date'],
            'end_at'        => ['sometimes', 'nullable', 'date'],
            'trigger_scope' => ['required', 'max:250', 'string'],

        ];
        if (!$this->strict) {
            $rules['state']            = ['required', Rule::enum(OfferComponentStateEnum::class)];
            $rules['start_at']         = ['sometimes', 'nullable', 'date'];
            $rules['is_discretionary'] = ['sometimes', 'boolean'];
            $rules['is_locked']        = ['sometimes', 'boolean'];
            $rules['source_data']      = ['sometimes', 'array'];

            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    /**
     * @throws \Throwable
     */
    public function action(Offer $offer, $trigger, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): OfferComponent
    {
        if (!$audit) {
            OfferComponent::disableAuditing();
        }
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisationFromShop($offer->shop, $modelData);

        return $this->handle($offer, $trigger, $this->validatedData);
    }
}
