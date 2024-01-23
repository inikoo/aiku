<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 13:06:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\StockFamily\UI;

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
