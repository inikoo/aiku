<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 29 Sep 2021 16:47:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Marketing\Product;

use App\Actions\Marketing\HistoricProduct\StoreHistoricProduct;
use App\Actions\Marketing\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateProducts;
use App\Models\Marketing\Product;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;
use App\Rules\CaseSensitive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreProduct
{
    use AsAction;
    use WithAttributes;

    private int $hydratorsDelay =0;

    public function handle(Shop|Product $parent, array $modelData, bool $skipHistoric = false): Product
    {
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
        $product->salesStats()->create([
            'scope' => 'sales'
        ]);
        /** @var Tenant $tenant */
        $tenant = app('currentTenant');
        if ($product->shop->currency_id != $tenant->currency_id) {
            $product->salesStats()->create([
                'scope' => 'sales-tenant-currency'
            ]);
        }


        ShopHydrateProducts::dispatch($product->shop);
        ProductHydrateUniversalSearch::dispatch($product);
        return $product;
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'unique:tenant.products', 'between:2,9', 'alpha', new CaseSensitive('products')],
            'family_id'   => ['sometimes', 'required', 'exists:families,id'],
            'units'       => ['sometimes', 'required', 'numeric'],
            'image_id'    => ['sometimes', 'required', 'exists:media,id'],
            'price'       => ['sometimes', 'required', 'numeric'],
            'rrp'         => ['sometimes', 'required', 'numeric'],
            'name'        => ['required', 'max:250', 'string'],
            'state'       => ['sometimes', 'required'],
            'owner_id'    => ['required', 'numeric'],
            'owner_type'  => ['required'],
            'type'        => ['required'],
            'description' => ['sometimes', 'required', 'max:1500'],
        ];
    }

    public function action(Shop|Product $parent, array $objectData): Product
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData);
    }

    public function inShop(Shop $shop, ActionRequest $request): RedirectResponse
    {
        $request->validate();

        $this->handle($shop, $request->all());
        return  Redirect::route('shops.show.catalogue.hub.products.index', $shop);
    }

    public function asFetch(Shop $shop, array $productData, int $hydratorsDelay=60): Product
    {
        $this->hydratorsDelay=$hydratorsDelay;
        return $this->handle($shop, $productData);
    }
}
