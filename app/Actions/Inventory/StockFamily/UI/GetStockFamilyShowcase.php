<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\StockFamily\UI;

use App\Models\Inventory\StockFamily;
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
