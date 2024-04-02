<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 00:32:29 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Market\Product\ProductStateEnum;
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
                    $productStocks[$tradeUnit->id] = [
                        'quantity' => $modelData->{'Product Part Ratio'} * $tradeUnit->pivot->quantity,
                        'notes'    => $modelData->{'Product Part Note'} ?? null
                    ];
                }
            } else {

                $product=$this->parseProduct($this->organisation->id.':'.$modelData->{'Product Part Product ID'});
                if($product->state!=ProductStateEnum::DISCONTINUED) {
                    print "\nWarning: Part SKU ".$modelData->{'Product Part Part SKU'}." not found in `Product Part Bridge`\n";
                    //abort(404, "Error fetching products-stock relation");
                }
            }
        }
        $this->parsedData['product_stocks'] = $productStocks;
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Product Part Bridge')
            ->where('Product Part Product ID', $id)->get();
    }
}
