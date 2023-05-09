<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier\Hydrators;

use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Models\Procurement\Supplier;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierHydrateSupplierDeliveries implements ShouldBeUnique
{
    use AsAction;

    public function handle(Supplier $supplier): void
    {
        $stats = [
            'number_supplier_deliveries' => $supplier->supplierDeliveries->count(),
        ];

        $supplierDeliveryStateCounts = $supplier->supplierDeliveries()
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PurchaseOrderStateEnum::cases() as $productState) {
            $stats['number_supplier_deliveries_state_'.$productState->snake()] = Arr::get($supplierDeliveryStateCounts, $productState->value, 0);
        }

        $supplierDeliveryStatusCounts =  $supplier->supplierDeliveries()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')->all();

        foreach (PurchaseOrderStatusEnum::cases() as $supplierDeliveryStatusEnum) {
            $stats['number_supplier_deliveries_status_'.$supplierDeliveryStatusEnum->snake()] = Arr::get($supplierDeliveryStatusCounts, $supplierDeliveryStatusEnum->value, 0);
        }

        $supplier->stats->update($stats);
    }

    public function getJobUniqueId(Supplier $supplier): int
    {
        return $supplier->id;
    }
}
