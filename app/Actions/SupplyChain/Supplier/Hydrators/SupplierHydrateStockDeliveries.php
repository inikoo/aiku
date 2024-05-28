<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 00:55:27 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier\Hydrators;

use App\Enums\Procurement\StockDelivery\StockDeliveryStateEnum;
use App\Enums\Procurement\StockDelivery\StockDeliveryStatusEnum;
use App\Models\SupplyChain\Supplier;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierHydrateStockDeliveries
{
    use AsAction;

    public function handle(Supplier $supplier): void
    {
        $stats = [
            'number_stock_deliveries' => $supplier->stockDeliveries->count(),
        ];

        $stockDeliveryStateCounts = $supplier->stockDeliveries()
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (StockDeliveryStateEnum::cases() as $productState) {
            $stats['number_stock_deliveries_state_'.$productState->snake()] = Arr::get($stockDeliveryStateCounts, $productState->value, 0);
        }

        $stockDeliveryStatusCounts =  $supplier->stockDeliveries()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')->all();

        foreach (StockDeliveryStatusEnum::cases() as $stockDeliveryStatusEnum) {
            $stats['number_stock_deliveries_status_'.$stockDeliveryStatusEnum->snake()] = Arr::get($stockDeliveryStatusCounts, $stockDeliveryStatusEnum->value, 0);
        }

        $supplier->stats()->update($stats);
    }


}
