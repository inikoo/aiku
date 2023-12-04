<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Enums\Procurement\PurchaseOrderItem\PurchaseOrderItemStatusEnum;
use App\Models\SysAdmin\Organisation;
use App\Models\Procurement\PurchaseOrder;
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
