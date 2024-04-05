<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 29 Sep 2021 16:47:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Market\Product;

use App\Actions\Market\HistoricProduct\StoreHistoricProduct;
use App\Actions\Market\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\Market\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\OrgAction;
use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\Product\ProductTypeEnum;
use App\Models\Market\Product;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreProduct extends OrgAction
{
    private $state=null;

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

        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'group_id', $parent->group_id);


        /** @var Product $product */
        $product = $parent->products()->create($modelData);
        $product->stats()->create();
        if (!$skipHistoric) {
            $historicProduct = StoreHistoricProduct::run($product);
            $product->update(
                [
                    'current_historic_product_id' => $historicProduct->id
                ]
            );
        }



        ShopHydrateProducts::dispatch($product->shop);
        ProductHydrateUniversalSearch::dispatch($product);

        return $product;
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
            'units'       => ['sometimes', 'required', 'numeric'],
            'image_id'    => ['sometimes', 'required', 'exists:media,id'],
            'price'       => ['required', 'numeric'],
            'rrp'         => ['sometimes', 'required', 'numeric'],
            'name'        => ['required', 'max:250', 'string'],
            'description' => ['sometimes', 'required', 'max:1500'],
            'source_id'   => ['sometimes', 'required', 'string', 'max:255'],
            'type'        => ['required', Rule::enum(ProductTypeEnum::class)],
            'owner_id'    => 'required',// todo check if this is needed
            'owner_type'  => 'required',
            'status'      => ['required', 'boolean'],
            'state'       => ['required', Rule::enum(ProductStateEnum::class)],
            'data'        => ['sometimes', 'array'],
            'settings'    => ['sometimes', 'array'],
            'created_at'  => ['sometimes', 'date'],
        ];

        if($this->state and $this->state==ProductStateEnum::DISCONTINUED) {
            $rules['code']= [
                'required',
                'max:32',
                'alpha_dash',
            ];
        }


        return $rules;

    }

    public function action(Shop|ProductCategory $parent, array $modelData, int $hydratorsDelay = 0, bool $skipHistoric = false): Product
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;
        $this->state          =Arr::get($modelData, 'state');
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
