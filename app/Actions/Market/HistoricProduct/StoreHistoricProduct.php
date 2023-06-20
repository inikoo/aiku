<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 29 Sep 2021 16:47:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Market\HistoricProduct;

use App\Models\Marketing\HistoricProduct;
use App\Models\Marketing\Product;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreHistoricProduct
{
    use AsAction;

    public function handle(Product $product, array $modelData = []): HistoricProduct
    {
        $historicProductData = [
            'code'       => Arr::get($modelData, 'code', $product->code),
            'name'       => Arr::get($modelData, 'name', $product->name),
            'price'      => Arr::get($modelData, 'price', $product->price),
            'units'      => Arr::get($modelData, 'units', $product->units),
            'source_id'  => Arr::get($modelData, 'source_id'),


        ];
        if (Arr::get($modelData, 'created_at')) {
            $historicProductData['created_at'] = Arr::get($modelData, 'created_at');
        } else {
            $historicProductData['created_at'] = $product->created_at;
        }
        if (Arr::get($modelData, 'deleted_at')) {
            $historicProductData['deleted_at'] = Arr::get($modelData, 'deleted_at');
        }
        if (Arr::exists($modelData, 'status')) {
            $historicProductData['status'] = Arr::exists($modelData, 'status');
        } else {
            $historicProductData['status'] = true;
        }

        /** @var HistoricProduct $historicProduct */
        $historicProduct = $product->historicRecords()->create($historicProductData);
        $historicProduct->stats()->create();

        return $historicProduct;
    }
}
