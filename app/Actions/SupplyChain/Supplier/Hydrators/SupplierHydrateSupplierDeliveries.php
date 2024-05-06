<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 00:55:27 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier\Hydrators;

use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStateEnum;
use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStatusEnum;
use App\Models\SupplyChain\Supplier;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierHydrateSupplierDeliveries
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

        foreach (SupplierDeliveryStateEnum::cases() as $productState) {
            $stats['number_supplier_deliveries_state_'.$productState->snake()] = Arr::get($supplierDeliveryStateCounts, $productState->value, 0);
        }

        $supplierDeliveryStatusCounts =  $supplier->supplierDeliveries()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')->all();

        foreach (SupplierDeliveryStatusEnum::cases() as $supplierDeliveryStatusEnum) {
            $stats['number_supplier_deliveries_status_'.$supplierDeliveryStatusEnum->snake()] = Arr::get($supplierDeliveryStatusCounts, $supplierDeliveryStatusEnum->value, 0);
        }

        $supplier->stats()->update($stats);
    }


}
