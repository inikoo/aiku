<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraProductStocks extends FetchAurora
{
    protected function parseModel(): void
    {
        $productStocks = [];
        foreach ($this->auroraModelData as $modelData) {
            $orgStock = $this->parseOrgStock($this->organisation->id.':'.$modelData->{'Product Part Part SKU'});

            if ($orgStock) {
                $productStocks[$orgStock->id] = [
                    'quantity' => $modelData->{'Product Part Ratio'},
                    'notes'    => $modelData->{'Product Part Note'} ?? null
                ];
            }

        }
        $this->parsedData['org_stocks'] = $productStocks;

    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product Part Bridge')
            ->where('Product Part Product ID', $id)->get();
    }
}
