<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Jul 2024 20:31:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Adjustment;

use App\Actions\Catalogue\Asset\StoreAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\OrgAction;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Models\Catalogue\Adjustment;
use App\Models\Catalogue\Shop;

class StoreAdjustment extends OrgAction
{
    public function handle(Shop $shop, array $modelData): Adjustment
    {

        data_set($modelData, 'status', true);
        data_set($modelData, 'organisation_id', $shop->organisation_id);
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'shop_id', $shop->id);
        data_set($modelData, 'currency_id', $shop->currency_id);


        /** @var Adjustment $adjustment */
        $adjustment = $shop->adjustments()->create($modelData);
        $adjustment->stats()->create();
        $adjustment->refresh();

        $asset = StoreAsset::run(
            $adjustment,
            [
                'type'  => AssetTypeEnum::ADJUSTMENT,
                'state' => AssetStateEnum::ACTIVE,
            ]
        );

        $adjustment->updateQuietly(
            [
                'asset_id' => $asset->id
            ]
        );

        $historicAsset = StoreHistoricAsset::run(
            $adjustment,
            [
                'source_id' => $adjustment->historic_source_id
            ]
        );
        $asset->update(
            [
                'current_historic_asset_id' => $historicAsset->id,
            ]
        );
        $adjustment->updateQuietly(
            [
                'current_historic_asset_id' => $historicAsset->id,
            ]
        );

        //ShopHydrateAdjustments::dispatch($shop);
        //OrganisationHydrateAdjustments::dispatch($shop->organisation);
        //GroupHydrateAdjustments::dispatch($shop->group);
        //AdjustmentHydrateUniversalSearch::dispatch($adjustment);


        return $adjustment;
    }

    public function rules(): array
    {
        return [
            'code'               => [
                'required',
                'max:32',
                'alpha_dash',
            ],
            'name'                     => ['required', 'max:250', 'string'],
            'price'                    => ['required', 'numeric', 'min:0'],
            'unit'                     => ['required', 'string'],
            'data'                     => ['sometimes', 'array'],
            'created_at'               => ['sometimes', 'date'],
            'source_id'                => ['sometimes', 'string', 'max:63'],
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
