<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:26:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\StockDeliveryItem;

use App\Models\Procurement\StockDelivery;
use App\Models\Procurement\StockDeliveryItem;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreStockDeliveryItem
{
    use AsAction;

    public function handle(StockDelivery $stockDelivery, array $modelData): StockDeliveryItem
    {
        /** @var StockDeliveryItem $stockDeliveryItem */
        $stockDeliveryItem = $stockDelivery->items()->create($modelData);

        return $stockDeliveryItem;
    }
}
