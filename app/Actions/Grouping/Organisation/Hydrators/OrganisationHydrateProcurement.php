<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Grouping\Organisation\Hydrators;

use App\Enums\Procurement\PurchaseOrderItem\PurchaseOrderItemStatusEnum;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Grouping\Organisation;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateProcurement
{
    use AsAction;

    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_purchase_orders' => PurchaseOrder::count()
        ];

        $purchaseOrderStatusCounts = PurchaseOrder::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')->all();

        foreach (PurchaseOrderItemStatusEnum::cases() as $purchaseOrderStatusEnum) {
            $stats['number_purchase_orders_status_'.$purchaseOrderStatusEnum->snake()] = Arr::get($purchaseOrderStatusCounts, $purchaseOrderStatusEnum->value, 0);
        }

        $organisation->procurementStats->update($stats);
    }
}
