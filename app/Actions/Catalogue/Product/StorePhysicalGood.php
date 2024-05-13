<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 29 Sep 2021 16:47:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\Outer\StoreOuter;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProducts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProducts;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateHistoricOuterables;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateOuters;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\OrgAction;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\Product\ProductTypeEnum;
use App\Enums\Catalogue\Product\ProductUnitRelationshipType;
use App\Models\Goods\TradeUnit;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StorePhysicalGood extends OrgAction
{
    use IsStoreProduct;

    private ProductStateEnum|null $state=null;
    private ProductCategory|Shop $parent;

    public function handle(Shop|ProductCategory $parent, array $modelData): Product
    {

        $modelData=$this->setDataFromParent($parent, $modelData);

        $tradeUnits = $modelData['trade_units'];
        data_forget($modelData, 'trade_units');

        data_set($modelData, 'unit_relationship_type', $this->getUnitRelationshipType($tradeUnits));
        data_set($modelData, 'outerable_type', 'Outer');

        $price=Arr::get($modelData, 'price');
        data_forget($modelData, 'price');


        /** @var Product $product */
        $product = $parent->products()->create($modelData);
        $product->stats()->create();
        $product->salesIntervals()->create();

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
                'code'              => $product->code,
                'price'             => $price,
                'name'              => $product->name,
                'is_main'           => true,
                'main_outer_ratio'  => 1,
                'source_id'         => $product->source_id,
                'historic_source_id'=> $product->historic_source_id
            ]
        );

        SetProductMainOuter::run(
            product: $product,
            mainOuter: $outer
        );

        ProductHydrateHistoricOuterables::dispatch($product);
        ProductHydrateOuters::dispatch($product);

        ShopHydrateProducts::dispatch($product->shop);
        OrganisationHydrateProducts::dispatch($product->organisation);
        GroupHydrateProducts::dispatch($product->group);

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


        if($this->state==ProductStateEnum::DISCONTINUED or !$this->strict) {
            $tradeUnitRules=[
                'trade_units' => ['sometimes','nullable', 'array'],
            ];
        } else {
            $tradeUnitRules=[
                'trade_units' => ['required', 'array'],
            ];
        }


        return array_merge(
            $this->getProductRules(),
            $tradeUnitRules,
            [
                'state' => ['required', Rule::enum(ProductStateEnum::class)],

            ]
        );

    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->set('type', ProductTypeEnum::PHYSICAL_GOOD);
        $this->prepareProductForValidation();

        if(!$this->has('state')) {
            $this->set('state', ProductStateEnum::IN_PROCESS);
        }

    }



    public function inShop(Shop $shop, ActionRequest $request): RedirectResponse
    {
        $request->validate();
        $this->handle($shop, $request->all());

        return Redirect::route('grp.org.shops.show.catalogue.products.index', $shop);
    }


}
