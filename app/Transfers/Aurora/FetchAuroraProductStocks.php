<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Transfers\Aurora\FetchAurora;
use Illuminate\Support\Facades\DB;

class FetchAuroraProductStocks extends FetchAurora
{
    protected function parseModel(): void
    {
        $productStocks = [];
        foreach ($this->auroraModelData as $modelData) {
            $orgStock   = $this->parseOrgStock($this->organisation->id.':'.$modelData->{'Product Part Part SKU'});

            if ($orgStock) {
                foreach ($orgStock->stock->tradeUnits as $tradeUnit) {
                    $productStocks[$tradeUnit->id]=[
                        'units'                     => $modelData->{'Product Part Ratio'} * $tradeUnit->pivot->quantity,
                        'notes'                     => $modelData->{'Product Part Note'} ?? null
                    ];


                }
            }
            //else {
            //print "Warning: Part SKU ".$modelData->{'Product Part Part SKU'}." not found in `Asset Part Bridge`\n";
            //}
        }
        $this->parsedData['trade_units'] = $productStocks;
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product Part Bridge')
            ->where('Product Part Product ID', $id)->get();
    }
}
