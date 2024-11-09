<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 11 Sept 2024 13:16:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Discounts\Offer;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateOffers;
use App\Actions\Discounts\OfferCampaign\Hydrators\OfferCampaignHydrateOffers;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateOffers;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOffers;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Discounts\Offer\OfferStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Discounts\Offer;
use App\Models\Discounts\OfferCampaign;
use App\Rules\IUnique;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreOffer extends OrgAction
{
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(OfferCampaign $offerCampaign, null|Shop|Product|ProductCategory|Customer $trigger, array $modelData): Offer
    {
        data_set($modelData, 'group_id', $offerCampaign->group_id);
        data_set($modelData, 'organisation_id', $offerCampaign->organisation_id);
        data_set($modelData, 'shop_id', $offerCampaign->shop_id);

        if ($trigger) {
            data_set($modelData, 'trigger_type', class_basename($trigger));
            data_set($modelData, 'trigger_id', $trigger->id);
        }
        $offer = DB::transaction(function () use ($offerCampaign, $modelData) {
            /** @var Offer $offer */
            $offer = $offerCampaign->offers()->create($modelData);
            $offer->stats()->create();
            return $offer;
        });
        GroupHydrateOffers::dispatch($offerCampaign->group)->delay($this->hydratorsDelay);
        OrganisationHydrateOffers::dispatch($offerCampaign->organisation)->delay($this->hydratorsDelay);
        ShopHydrateOffers::dispatch($offerCampaign->shop)->delay($this->hydratorsDelay);
        OfferCampaignHydrateOffers::dispatch($offerCampaign)->delay($this->hydratorsDelay);

        return $offer;
    }

    public function rules(): array
    {
        $rules = [
            'code'         => [
                'required',
                new IUnique(
                    table: 'offers',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                    ]
                ),

                'max:64',
                'alpha_dash'
            ],
            'name'         => ['required', 'max:250', 'string'],
            'data'         => ['sometimes', 'required'],
            'settings'     => ['sometimes', 'required'],
            'allowances'   => ['sometimes', 'required'],
            'start_at'     => ['sometimes', 'date'],
            'end_at'       => ['sometimes', 'nullable', 'date'],
            'type'         => ['required', 'string'],
            'trigger_type' => ['sometimes', Rule::in(['Order'])],
            'state'        => ['sometimes', Rule::enum(OfferStateEnum::class)],
            'status'       => ['sometimes', 'boolean'],
        ];
        if (!$this->strict) {
            $rules['start_at']   = ['sometimes', 'nullable', 'date'];
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    /**
     * @throws \Throwable
     */
    public function action(OfferCampaign $offerCampaign, null|Shop|Product|ProductCategory|Customer $trigger, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): Offer
    {
        if (!$audit) {
            Offer::disableAuditing();
        }
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisationFromShop($offerCampaign->shop, $modelData);

        return $this->handle($offerCampaign, $trigger, $this->validatedData);
    }
}
