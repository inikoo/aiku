<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 00:32:29 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraProductStocks extends FetchAurora
{
    protected function parseModel(): void
    {
        $productStocks = [];
        foreach($this->auroraModelData as $modelData){
           $stock= \App\Actions\SourceFetch\Aurora\FetchStocks::run($this->tenantSource, $modelData->{'Product Part Part SKU'});
            foreach ($stock->tradeUnits as $tradeUnit) {
                $productStocks[$tradeUnit->id] = [
                    'quantity' => $tradeUnit->pivot->quantity,
                    'notes'    => $modelData->{'Product Part Note'} ?? null
                ];
            }
        }
        $this->parsedData['product_stocks']=$productStocks;

    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product Part Bridge')
            ->where('Product Part Product ID', $id)->get();
    }

}
