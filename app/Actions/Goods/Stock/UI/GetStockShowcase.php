<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Aug 2024 17:07:40 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Stock\UI;

use App\Models\SupplyChain\Stock;
use Lorisleiva\Actions\Concerns\AsObject;

class GetStockShowcase
{
    use AsObject;

    public function handle(Stock $stock): array
    {
        // $numberLocations = 0;
        // $quantityLocations = 0;
        // foreach ($stock->orgStocks as $orgStock)
        // {
        //     $num = $orgStock->locationOrgStocks()->count();
        //     $quant = $orgStock->quantity_in_locations;
        //     $quantityLocations = $quantityLocations + $quant;
        //     $numberLocations = $numberLocations + $num;
        // }

        return [
            // 'contactCard' => [
            //     'id' => $stock->id,
            //     'slug'  => $stock->slug,
            //     'code'  => $stock->slug,
            //     'unit_value' => $stock->unit_value,
            //     'description' => $stock->description,
            //     'number_locations' => $numberLocations,
            //     'quantity_locations' => $quantityLocations,
            //     'photo' => $stock->imageSources(),

            // ]
        ];
    }
}
