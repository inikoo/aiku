<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 19:05:20 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\Asset\StoreAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\ProductVariant\StoreProductVariant;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProducts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProducts;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\Product\ProductUnitRelationshipType;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\Goods\TradeUnit;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreProduct extends OrgAction
{
    private AssetStateEnum|null $state = null;

    public function handle(Shop|ProductCategory $parent, array $modelData): Product
    {
        $status = false;
        if (in_array(Arr::get($modelData, 'state'), [ProductStateEnum::ACTIVE, ProductStateEnum::DISCONTINUING])) {
            $status = true;
        }
        data_set($modelData, 'status', $status);


        $tradeUnits = $modelData['trade_units'];
        data_forget($modelData, 'trade_units');

        if (count($tradeUnits) == 1) {
            $units = $tradeUnits[array_key_first($tradeUnits)]['units'];
        } else {
            $units = 1;
        }
        data_set($modelData, 'units', $units);


        data_set($modelData, 'unit_relationship_type', $this->getUnitRelationshipType($tradeUnits));


        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'group_id', $parent->group_id);

        if ($parent instanceof Shop) {
            $shop = $parent;
            data_set($modelData, 'shop_id', $parent->id);
        } else {
            $shop = $parent->shop;
            data_set($modelData, 'shop_id', $parent->shop_id);
            if ($parent->type == ProductCategoryTypeEnum::FAMILY) {
                data_set($modelData, 'family_id', $parent->id);
            }
        }
        data_set($modelData, 'currency_id', $shop->currency_id);


        /** @var Product $product */
        $product = $shop->products()->create($modelData);
        $product->stats()->create();
        $product->salesIntervals()->create();
        $product->refresh();

        foreach ($tradeUnits as $tradeUnitId => $tradeUnitData) {
            $tradeUnit = TradeUnit::find($tradeUnitId);
            $product->tradeUnits()->attach(
                $tradeUnit,
                [
                    'units' => $tradeUnitData['units'],
                    'notes' => Arr::get($tradeUnitData, 'notes'),
                ]
            );
        }

        StoreProductVariant::run(
            $product,
            [
                'is_main'            => true,
                'ratio'              => 1,
                'code'               => $product->code,
                'name'               => $product->name,
                'price'              => $product->price,
                'state'              => $product->state,
                'source_id'          => $product->source_id,
                'historic_source_id' => $product->historic_source_id,
            ]
        );
        $product->refresh();


        $asset = StoreAsset::run(
            $product,
            [
                'type'  => AssetTypeEnum::PRODUCT,
                'state' => match ($product->state) {
                    ProductStateEnum::IN_PROCESS    => AssetStateEnum::IN_PROCESS,
                    ProductStateEnum::ACTIVE        => AssetStateEnum::ACTIVE,
                    ProductStateEnum::DISCONTINUING => AssetStateEnum::DISCONTINUING,
                    ProductStateEnum::DISCONTINUED  => AssetStateEnum::DISCONTINUED,
                }
            ]
        );

        $product->updateQuietly(
            [
                'asset_id' => $asset->id
            ]
        );

        $historicOuterable = StoreHistoricAsset::run(
            $product,
            [
                'source_id' => $product->historic_source_id
            ]
        );
        $asset->update(
            [
                'current_historic_asset_id' => $historicOuterable->id,
            ]
        );


        OrganisationHydrateProducts::dispatch($product->organisation);
        GroupHydrateProducts::dispatch($product->group);
        ShopHydrateProducts::dispatch($product->shop);

        return $product;
    }

    public function getUnitRelationshipType(array $tradeUnits): ?ProductUnitRelationshipType
    {
        if (count($tradeUnits) == 1) {
            return ProductUnitRelationshipType::SINGLE;
        } elseif (count($tradeUnits) > 1) {
            return ProductUnitRelationshipType::MULTIPLE;
        }

        return null;
    }

    public function rules(): array
    {
        $rules = [
            'code'               => [
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'assets',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'state', 'operator' => '!=', 'value' => ProductStateEnum::DISCONTINUED->value],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'name'               => ['required', 'max:250', 'string'],
            'state'              => ['sometimes', 'required', Rule::enum(ProductStateEnum::class)],
            'family_id'          => ['sometimes', 'required', 'exists:families,id'],
            'image_id'           => ['sometimes', 'required', 'exists:media,id'],
            'price'              => ['required', 'numeric', 'min:0'],
            'unit'               => ['sometimes', 'required', 'string'],
            'rrp'                => ['sometimes', 'required', 'numeric', 'min:0'],
            'description'        => ['sometimes', 'required', 'max:1500'],
            'source_id'          => ['sometimes', 'required', 'string', 'max:255'],
            'historic_source_id' => ['sometimes', 'required', 'string', 'max:255'],
            'data'               => ['sometimes', 'array'],
            'settings'           => ['sometimes', 'array'],
            'created_at'         => ['sometimes', 'date'],

        ];

        if ($this->state == ProductStateEnum::DISCONTINUED) {
            $rules['code'] = [
                'required',
                'max:32',
                'alpha_dash',
            ];
        }

        if ($this->state == ProductStateEnum::DISCONTINUED or !$this->strict) {
            $rules['trade_units'] = ['sometimes', 'nullable', 'array'];
        } else {
            $rules['trade_units'] = ['required', 'array'];
        }


        return $rules;
    }


    public function inShop(Shop $shop, ActionRequest $request): RedirectResponse
    {
        $request->validate();
        $this->handle($shop, $request->all());

        return Redirect::route('grp.org.shops.show.catalogue.products.index', $shop);
    }

    public function action(Shop|ProductCategory $parent, array $modelData, int $hydratorsDelay = 0, $strict = true): Product
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;
        $this->strict         = $strict;

        if ($parent instanceof Shop) {
            $shop = $parent;
        } else {
            $shop = $parent->shop;
        }

        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

}
