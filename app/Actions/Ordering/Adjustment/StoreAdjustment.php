<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 03 Sept 2024 12:14:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Adjustment;

use App\Actions\Helpers\CurrencyExchange\GetCurrencyExchange;
use App\Actions\OrgAction;
use App\Enums\Ordering\Adjustment\AdjustmentTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Ordering\Adjustment;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreAdjustment extends OrgAction
{
    public function handle(Shop $shop, array $modelData): Adjustment
    {
        data_set($modelData, 'organisation_id', $shop->organisation_id);
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'shop_id', $shop->id);
        data_set($modelData, 'currency_id', $shop->currency_id);

        if (!Arr::exists($modelData, 'org_net_amount')) {
            $modelData['org_net_amount'] = $modelData['net_amount'] * GetCurrencyExchange::run($shop->currency, $shop->organisation->currency);
        }
        if (!Arr::exists($modelData, 'grp_net_amount')) {
            $modelData['grp_net_amount'] = $modelData['net_amount'] * GetCurrencyExchange::run($shop->currency, $shop->group->currency);
        }

        if (Arr::exists($modelData, 'tax_amount')) {
            if (!Arr::exists($modelData, 'org_tax_amount')) {
                $modelData['org_tax_amount'] = $modelData['tax_amount'] * GetCurrencyExchange::run($shop->currency, $shop->organisation->currency);
            }
            if (!Arr::exists($modelData, 'grp_tax_amount')) {
                $modelData['grp_tax_amount'] = $modelData['tax_amount'] * GetCurrencyExchange::run($shop->currency, $shop->group->currency);
            }
        }


        /** @var Adjustment $adjustment */
        $adjustment = $shop->adjustments()->create($modelData);

        //ShopHydrateAdjustments::dispatch($shop);
        //OrganisationHydrateAdjustments::dispatch($shop->organisation);
        //GroupHydrateAdjustments::dispatch($shop->group);
        //AdjustmentHydrateUniversalSearch::dispatch($adjustment);


        return $adjustment;
    }

    public function rules(): array
    {
        return [
            'type'           => ['required', Rule::enum(AdjustmentTypeEnum::class)],
            'net_amount'     => ['required', 'numeric'],
            'org_net_amount' => ['sometimes', 'numeric'],
            'grp_net_amount' => ['sometimes', 'numeric'],
            'tax_amount'     => ['sometimes', 'nullable', 'numeric'],
            'org_tax_amount' => ['sometimes', 'nullable', 'numeric'],
            'grp_tax_amount' => ['sometimes', 'nullable', 'numeric'],
            'source_id'      => ['sometimes', 'string', 'max:64'],
            'fetched_at'     => ['sometimes', 'date'],
        ];
    }

    public function action(Shop $shop, array $modelData, int $hydratorsDelay = 0): Adjustment
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;


        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $this->validatedData);
    }


}
