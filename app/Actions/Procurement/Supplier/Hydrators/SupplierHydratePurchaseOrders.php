<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 04 May 2023 14:25:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier\Hydrators;

use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Models\Procurement\Supplier;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierHydratePurchaseOrders implements ShouldBeUnique
{
    use AsAction;

    public function handle(Supplier $supplier): void
    {
        $stats = [
            'number_purchase_orders' => $supplier->purchaseOrders->count(),
        ];

        $purchaseOrderStateCounts = $supplier->purchaseOrders()
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PurchaseOrderStateEnum::cases() as $productState) {
            $stats['number_purchase_orders_state_'.$productState->snake()] = Arr::get($purchaseOrderStateCounts, $productState->value, 0);
        }

        $purchaseOrderStatusCounts =  $supplier->purchaseOrders()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')->all();

        foreach (PurchaseOrderStatusEnum::cases() as $purchaseOrderStatusEnum) {
            $stats['number_purchase_orders_status_'.$purchaseOrderStatusEnum->snake()] = Arr::get($purchaseOrderStatusCounts, $purchaseOrderStatusEnum->value, 0);
        }

        $supplier->stats->update($stats);
    }

    public function getJobUniqueId(Supplier $supplier): int
    {
        return $supplier->id;
    }
}
