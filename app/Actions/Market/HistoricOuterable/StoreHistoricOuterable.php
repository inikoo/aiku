<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 11:05:48 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\HistoricOuterable;

use App\Models\Market\HistoricOuterable;
use App\Models\Market\Outer;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreHistoricOuterable
{
    use AsAction;

    public function handle(Outer $outer, array $modelData = []): HistoricOuterable
    {
        $historicProductData = [
            'code'       => Arr::get($modelData, 'code', $outer->code),
            'name'       => Arr::get($modelData, 'name', $outer->name),
            'price'      => Arr::get($modelData, 'price', $outer->price),
            'units'      => Arr::get($modelData, 'units', $outer->units),
            'source_id'  => Arr::get($modelData, 'source_id'),
        ];
        if (Arr::get($modelData, 'created_at')) {
            $historicProductData['created_at'] = Arr::get($modelData, 'created_at');
        } else {
            $historicProductData['created_at'] = $outer->created_at;
        }
        if (Arr::get($modelData, 'deleted_at')) {
            $historicProductData['deleted_at'] = Arr::get($modelData, 'deleted_at');
        }
        if (Arr::exists($modelData, 'status')) {
            $historicProductData['status'] = Arr::exists($modelData, 'status');
        } else {
            $historicProductData['status'] = true;
        }

        data_set($historicProductData, 'organisation_id', $outer->organisation_id);
        data_set($historicProductData, 'group_id', $outer->group_id);
        data_set($historicProductData, 'product_id', $outer->product_id);

        /** @var HistoricOuterable $historicProduct */
        $historicProduct = $outer->historicRecords()->create($historicProductData);
        $historicProduct->stats()->create();

        return $historicProduct;
    }
}
