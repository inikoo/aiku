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
        return [
            []
        ];
    }
}
