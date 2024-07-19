<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Apr 2024 12:42:18 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Charge;

use App\Actions\Catalogue\Asset\StoreAsset;
use App\Actions\Catalogue\Charge\Hydrators\ChargeHydrateUniversalSearch;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCharges;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateCharges;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCharges;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Catalogue\Charge\ChargeStateEnum;
use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Shop;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreCharge extends OrgAction
{
    public function handle(Shop $shop, array $modelData): Charge
    {
        $status = false;
        if (Arr::get($modelData, 'state') == ChargeStateEnum::ACTIVE) {
            $status = true;
        }
        data_set($modelData, 'status', $status);
        data_set($modelData, 'organisation_id', $shop->organisation_id);
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'shop_id', $shop->id);
        data_set($modelData, 'currency_id', $shop->currency_id);


        /** @var Charge $charge */
        $charge = $shop->charges()->create($modelData);
        $charge->stats()->create();
        $charge->refresh();

        $asset = StoreAsset::run(
            $charge,
            [
                'type'  => AssetTypeEnum::CHARGE,
                'state' => match ($charge->state) {
                    ChargeStateEnum::IN_PROCESS   => AssetStateEnum::IN_PROCESS,
                    ChargeStateEnum::ACTIVE       => AssetStateEnum::ACTIVE,
                    ChargeStateEnum::DISCONTINUED => AssetStateEnum::DISCONTINUED,
                }
            ]
        );

        $charge->updateQuietly(
            [
                'asset_id' => $asset->id
            ]
        );

        $historicAsset = StoreHistoricAsset::run(
            $charge,
            [
                'source_id' => $charge->historic_source_id
            ]
        );
        $asset->update(
            [
                'current_historic_asset_id' => $historicAsset->id,
            ]
        );
        $charge->updateQuietly(
            [
                'current_historic_asset_id' => $historicAsset->id,
            ]
        );

        ShopHydrateCharges::dispatch($shop);
        OrganisationHydrateCharges::dispatch($shop->organisation);
        GroupHydrateCharges::dispatch($shop->group);
        ChargeHydrateUniversalSearch::dispatch($charge);


        return $charge;
    }

    public function rules(): array
    {
        return [
            'code'               => [
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'charges',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'state', 'operator' => '!=', 'value' => ChargeStateEnum::DISCONTINUED->value],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'name'                     => ['required', 'max:250', 'string'],
            'price'                    => ['required', 'numeric', 'min:0'],
            'unit'                     => ['required', 'string'],
            'state'                    => ['sometimes', 'required', Rule::enum(ChargeStateEnum::class)],
            'data'                     => ['sometimes', 'array'],
            'created_at'               => ['sometimes', 'date'],
            'source_id'                => ['sometimes', 'string', 'max:63']
        ];
    }

    public function action(Shop $shop, array $modelData, int $hydratorsDelay = 0): Charge
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;


        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $this->validatedData);
    }


}