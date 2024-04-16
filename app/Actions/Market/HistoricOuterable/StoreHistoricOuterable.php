<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 11:05:48 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\HistoricOuterable;

use App\Models\Market\HistoricOuterable;
use App\Models\Market\Outer;
use App\Models\Market\Rental;
use App\Models\Market\Service;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreHistoricOuterable
{
    use AsAction;

    public function handle(Outer|Rental|Service $outerable, array $modelData = []): HistoricOuterable
    {

        $historicOuterableData = [
            'code'       => Arr::get($modelData, 'code', $outerable->code),
            'name'       => Arr::get($modelData, 'name', $outerable->name),
            'price'      => Arr::get($modelData, 'price', $outerable->price),
            'source_id'  => Arr::get($modelData, 'source_id'),
        ];

        if($outerable instanceof Outer) {
            data_set($historicOuterableData, 'price', $outerable->price);
        } else {
            data_set($historicOuterableData, 'price', $outerable->product->price);
        }


        if (Arr::get($modelData, 'created_at')) {
            $historicOuterableData['created_at'] = Arr::get($modelData, 'created_at');
        } else {
            $historicOuterableData['created_at'] = $outerable->created_at;
        }
        if (Arr::get($modelData, 'deleted_at')) {
            $historicOuterableData['deleted_at'] = Arr::get($modelData, 'deleted_at');
        }
        if (Arr::exists($modelData, 'status')) {
            $historicOuterableData['status'] = Arr::exists($modelData, 'status');
        } else {
            $historicOuterableData['status'] = true;
        }


        data_set($historicOuterableData, 'organisation_id', $outerable->organisation_id);
        data_set($historicOuterableData, 'group_id', $outerable->group_id);
        data_set($historicOuterableData, 'product_id', $outerable->product_id);

        /** @var HistoricOuterable $historicOuterable */
        $historicOuterable = $outerable->historicRecords()->create($historicOuterableData);
        $historicOuterable->stats()->create();

        return $historicOuterable;
    }
}
