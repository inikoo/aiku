<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\StockFamily\UI;

use App\Models\SupplyChain\StockFamily;
use Lorisleiva\Actions\Concerns\AsObject;

class GetStockFamilyShowcase
{
    use AsObject;

    public function handle(StockFamily $stockFamily): array
    {
        return [
            []
        ];
    }
}
