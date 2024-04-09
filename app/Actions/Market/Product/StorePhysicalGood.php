<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 29 Sep 2021 16:47:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Market\Product;

use App\Actions\Market\Outer\StoreOuter;
use App\Actions\Market\Product\Hydrators\ProductHydrateHistoricOuters;
use App\Actions\Market\Product\Hydrators\ProductHydrateOuters;
use App\Actions\Market\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\Market\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\OrgAction;
use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\Product\ProductTypeEnum;
use App\Enums\Market\Product\ProductUnitRelationshipType;
use App\Models\Goods\TradeUnit;
use App\Models\Market\Product;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StorePhysicalGood extends OrgAction
{
    private ProductStateEnum|null $state=null;
    private ProductCategory|Shop $parent;

    public function handle(Shop|ProductCategory $parent, array $modelData, bool $skipHistoric = false): Product
    {
        if (class_basename($parent) == 'Shop') {
            $modelData['shop_id']     = $parent->id;
            $modelData['parent_id']   = $parent->id;
            $modelData['parent_type'] = $parent->type;
            $modelData['owner_id']    = $parent->id;
            $modelData['owner_type']  = $parent->type;
        } else {
            $modelData['shop_id']    = $parent->shop_id;
            $modelData['owner_id']   = $parent->parent_id;
            $modelData['owner_type'] = $parent->shop->type;
        }

        $tradeUnits = $modelData['trade_units'];
        data_forget($modelData, 'trade_units');

        data_set($modelData, 'unit_relationship_type', $this->getUnitRelationshipType($tradeUnits));


        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'group_id', $parent->group_id);

        $price=Arr::get($modelData, 'price');
        data_forget($modelData, 'price');


        /** @var Product $product */
        $product = $parent->products()->create($modelData);
        $product->stats()->create();



        foreach ($tradeUnits as $tradeUnitId=>$tradeUnitData) {

            $tradeUnit=TradeUnit::find($tradeUnitId);

            $product->tradeUnits()->attach(
                $tradeUnit,
                [
                    'units_per_main_outer' => $tradeUnitData['units_per_main_outer'],
                    'notes'                => Arr::get($tradeUnitData, 'notes'),
                ]
            );


        }

        $outer=StoreOuter::run(
            product: $product,
            modelData: [
                'code'            => $product->code,
                'price'           => $price,
                'name'            => $product->name,
                'is_main'         => true,
                'main_outer_ratio'=> 1
            ],
            skipHistoric: $skipHistoric
        );

        SetProductMainOuter::run(
            product: $product,
            mainOuter: $outer
        );

        ProductHydrateHistoricOuters::dispatch($product);
        ProductHydrateOuters::dispatch($product);

        ShopHydrateProducts::dispatch($product->shop);
        ProductHydrateUniversalSearch::dispatch($product);

        return $product;
    }

    public function getUnitRelationshipType(array $tradeUnits): ?ProductUnitRelationshipType
    {
        if(count($tradeUnits)==1) {
            return ProductUnitRelationshipType::SINGLE;
        } elseif(count($tradeUnits)>1) {
            return ProductUnitRelationshipType::MULTIPLE;
        }
        return null;

    }

    public function rules(): array
    {
        $rules= [
            'code'        => [
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'products',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'value' => null],
                    ]
                ),
            ],
            'family_id'   => ['sometimes', 'required', 'exists:families,id'],
            'image_id'    => ['sometimes', 'required', 'exists:media,id'],
            'price'       => ['required', 'numeric','min:0'],

            'rrp'         => ['sometimes', 'required', 'numeric','min:0'],
            'name'        => ['required', 'max:250', 'string'],
            'description' => ['sometimes', 'required', 'max:1500'],
            'source_id'   => ['sometimes', 'required', 'string', 'max:255'],
            'type'        => ['required', Rule::enum(ProductTypeEnum::class)],
            'owner_id'    => 'required',
            'owner_type'  => 'required',
            'status'      => ['required', 'boolean'],
            'state'       => ['required', Rule::enum(ProductStateEnum::class)],
            'data'        => ['sometimes', 'array'],
            'settings'    => ['sometimes', 'array'],
            'created_at'  => ['sometimes', 'date'],
            'trade_units' => ['required', 'array'],

        ];

        if($this->state==ProductStateEnum::DISCONTINUED) {
            $rules['code']= [
                'required',
                'max:32',
                'alpha_dash',
            ];
        }


        return $rules;

    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->set('type', ProductTypeEnum::PHYSICAL_GOOD);
        if($this->parent instanceof ProductCategory) {
            $this->fill(
                [
                    'owner_type' => 'Shop',
                    'owner_id'   => $this->parent->shop_id
                ]
            );
        } elseif($this->parent instanceof Shop) {
            $this->fill(
                [
                    'owner_type' => 'Shop',
                    'owner_id'   => $this->parent->id
                ]
            );
        }

        if(!$this->has('status')) {
            $this->set('status', true);
        }

        if(!$this->has('state')) {
            $this->set('state', ProductStateEnum::IN_PROCESS);
        }


    }

    public function action(Shop|ProductCategory $parent, array $modelData, int $hydratorsDelay = 0, bool $skipHistoric = false): Product
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;
        $this->state          =Arr::get($modelData, 'state');
        $this->parent         =$parent;

        if ($parent instanceof Shop) {
            $shop = $parent;
        } else {
            $shop = $parent->shop;
        }

        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($parent, $this->validatedData, $skipHistoric);
    }

    public function inShop(Shop $shop, ActionRequest $request): RedirectResponse
    {
        $request->validate();
        $this->handle($shop, $request->all());

        return Redirect::route('grp.org.shops.show.catalogue.products.index', $shop);
    }


}
