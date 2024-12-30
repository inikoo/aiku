<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Dec 2024 11:48:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraMasterAssetHasStock extends FetchAurora
{
    protected function parseModel(): void
    {
        $masterProductStocks = [];
        foreach ($this->auroraModelData as $modelData) {

            $stock = $this->parseStock($this->organisation->id.':'.$modelData->{'Product Part Part SKU'});

            if ($stock) {
                $masterProductStocks[$stock->id] = [
                    'quantity'        => $modelData->{'Product Part Ratio'},
                    'notes'           => $modelData->{'Product Part Note'} ?? null,
                    'source_id'       => $this->organisation->id.':'.$modelData->{'Product Part Key'},
                    'last_fetched_at' => now(),
                ];
            }
        }
        $this->parsedData['stocks'] = $masterProductStocks;
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product Part Bridge')
            ->where('Product Part Product ID', $id)->get();
    }
}
