<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 11:05:48 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\HistoricProduct;

use App\Models\Market\HistoricProduct;
use App\Models\Market\Product;
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

        data_set($historicProductData, 'organisation_id', $product->organisation_id);
        data_set($historicProductData, 'group_id', $product->group_id);

        /** @var HistoricProduct $historicProduct */
        $historicProduct = $product->historicRecords()->create($historicProductData);
        $historicProduct->stats()->create();

        return $historicProduct;
    }
}
