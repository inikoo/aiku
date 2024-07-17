<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Jul 2024 20:31:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shipping;

use App\Actions\Catalogue\Asset\StoreAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Shipping\Hydrators\ShippingHydrateUniversalSearch;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateShippings;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateShippings;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateShippings;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Catalogue\Shipping\ShippingStateEnum;
use App\Models\Catalogue\Shipping;
use App\Models\Catalogue\Shop;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreShipping extends OrgAction
{
    public function handle(Shop $shop, array $modelData): Shipping
    {
        $status = false;
        if (Arr::get($modelData, 'state') == ShippingStateEnum::ACTIVE) {
            $status = true;
        }
        data_set($modelData, 'status', $status);
        data_set($modelData, 'organisation_id', $shop->organisation_id);
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'shop_id', $shop->id);
        data_set($modelData, 'currency_id', $shop->currency_id);


        /** @var Shipping $shipping */
        $shipping = $shop->shippings()->create($modelData);
        $shipping->stats()->create();
        $shipping->refresh();

        $asset = StoreAsset::run(
            $shipping,
            [
                'type'  => AssetTypeEnum::SHIPPING,
                'state' => match ($shipping->state) {
                    ShippingStateEnum::IN_PROCESS   => AssetStateEnum::IN_PROCESS,
                    ShippingStateEnum::ACTIVE       => AssetStateEnum::ACTIVE,
                    ShippingStateEnum::DISCONTINUED => AssetStateEnum::DISCONTINUED,
                }
            ]
        );

        $shipping->updateQuietly(
            [
                'asset_id' => $asset->id
            ]
        );

        $historicAsset = StoreHistoricAsset::run(
            $shipping,
            [
                'source_id' => $shipping->historic_source_id
            ]
        );
        $asset->update(
            [
                'current_historic_asset_id' => $historicAsset->id,
            ]
        );
        $shipping->updateQuietly(
            [
                'current_historic_asset_id' => $historicAsset->id,
            ]
        );

        ShopHydrateShippings::dispatch($shop);
        OrganisationHydrateShippings::dispatch($shop->organisation);
        GroupHydrateShippings::dispatch($shop->group);
        ShippingHydrateUniversalSearch::dispatch($shipping);


        return $shipping;
    }

    public function rules(): array
    {
        return [
            'code'               => [
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'shippings',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'state', 'operator' => '!=', 'value' => ShippingStateEnum::DISCONTINUED->value],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'name'                     => ['required', 'max:250', 'string'],
            'price'                    => ['required', 'numeric', 'min:0'],
            'unit'                     => ['required', 'string'],
            'state'                    => ['sometimes', 'required', Rule::enum(ShippingStateEnum::class)],
            'data'                     => ['sometimes', 'array'],
            'created_at'               => ['sometimes', 'date'],
            'source_id'                => ['sometimes', 'string', 'max:63'],
            'structural'               => ['sometimes', 'boolean'],
        ];
    }

    public function action(Shop $shop, array $modelData, int $hydratorsDelay = 0): Shipping
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;


        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $this->validatedData);
    }


}
