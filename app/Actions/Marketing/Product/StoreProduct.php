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
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreProduct
{
    use AsAction;
    use WithAttributes;

    public function handle(Shop $shop, array $modelData, bool $skipHistoric = false): Product
    {
        /** @var Product $product */
        $product = $shop->products()->create($modelData);
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
            'code'        => ['required', 'unique:tenant.products', 'between:2,9', 'alpha'],
            'family_id'   => ['sometimes', 'required', 'exists:families,id'],
            'units'       => ['sometimes', 'required', 'numeric'],
            'image_id'    => ['sometimes', 'required', 'exists:media,id'],
            'price'       => ['sometimes', 'required', 'numeric'],
            'rrp'         => ['sometimes', 'required', 'numeric'],
            'name'        => ['required', 'max:250', 'string'],
            'state'       => ['sometimes', 'required'],
            'owner_id'    => ['required'],
            'owner_type'  => ['required'],
            'description' => ['sometimes', 'required', 'max:1500'],
        ];
    }

    public function action(Shop $shop, array $objectData): Product
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shop, $validatedData);
    }
}
