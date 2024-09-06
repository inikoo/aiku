<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 19:05:20 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\Asset\StoreAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateProductVariants;
use App\Actions\Catalogue\ProductCategory\Hydrators\DepartmentHydrateProducts;
use App\Actions\Catalogue\ProductCategory\Hydrators\FamilyHydrateProducts;
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
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDot;
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
            data_set($modelData, 'department_id', $parent->department_id);

            if ($parent->type == ProductCategoryTypeEnum::FAMILY) {
                data_set($modelData, 'family_id', $parent->id);
            }
            if ($parent->type == ProductCategoryTypeEnum::DEPARTMENT) {
                data_set($modelData, 'department_id', $parent->id);
            }
        }
        data_set($modelData, 'currency_id', $shop->currency_id);


        /** @var Product $product */
        $product = $shop->products()->create($modelData);

        if ($product->is_main) {
            $product->updateQuietly([
                'main_product_id' => $product->id
            ]);
        }

        $product->stats()->create();
        $product->salesIntervals()->create();
        ProductHydrateProductVariants::dispatch($product->mainProduct)->delay($this->hydratorsDelay);

        $product->refresh();


        $asset = StoreAsset::run(
            $product,
            [
                'type'    => AssetTypeEnum::PRODUCT,
                'is_main' => $product->is_main,
                'state'   => match ($product->state) {
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


        $historicAsset = StoreHistoricAsset::run(
            $product,
            [
                'source_id' => $product->historic_source_id
            ]
        );

        $asset->updateQuietly(
            [
                'current_historic_asset_id' => $historicAsset->id,
            ]
        );
        $product->updateQuietly(
            [
                'current_historic_asset_id' => $historicAsset->id,
            ]
        );


        GroupHydrateProducts::dispatch($product->group)->delay($this->hydratorsDelay);
        OrganisationHydrateProducts::dispatch($product->organisation)->delay($this->hydratorsDelay);
        ShopHydrateProducts::dispatch($product->shop)->delay($this->hydratorsDelay);
        if ($product->department_id) {
            DepartmentHydrateProducts::dispatch($product->department)->delay($this->hydratorsDelay);
        }
        if ($product->family_id) {
            FamilyHydrateProducts::dispatch($product->family)->delay($this->hydratorsDelay);
        }

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
                new AlphaDashDot(),
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
            'family_id'          => [
                'sometimes',
                'required',
                Rule::exists('product_categories', 'id')
                    ->where('shop_id', $this->shop->id)
                    ->where('type', ProductCategoryTypeEnum::FAMILY)
            ],
            'department_id'      => [
                'sometimes',
                'required',
                Rule::exists('product_categories', 'id')
                    ->where('shop_id', $this->shop->id)
                    ->where('type', ProductCategoryTypeEnum::DEPARTMENT)
            ],
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
            'is_main'            => ['required', 'boolean'],
            'main_product_id'    => [
                'sometimes',
                'nullable',
                Rule::exists('products', 'id')
                    ->where('shop_id', $this->shop->id)
            ],
            'variant_ratio'      => ['sometimes', 'required', 'numeric', 'gt:0'],
            'variant_is_visible' => ['sometimes', 'required', 'boolean'],

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
        $this->initialisationFromShop($shop, $request);
        $this->handle($shop, $this->validatedData);

        return Redirect::route('grp.org.shops.show.catalogue.products.index', $shop);
    }

    public function inFamily(Organisation $organisation, Shop $shop, ProductCategory $family, ActionRequest $request): RedirectResponse
    {
        $this->initialisationFromShop($shop, $request);
        $this->handle($family, $this->validatedData);

        return Redirect::route('grp.org.shops.show.catalogue.families.show.products.index', [$organisation->slug, $shop->slug, $family->slug]);
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
