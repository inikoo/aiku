<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 May 2024 10:42:24 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplier\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderDeliveryStatusEnum;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgSupplierHydratePurchaseOrders
{
    use AsAction;
    use WithEnumStats;
    private OrgSupplier $orgSupplier;


    public function __construct(OrgSupplier $orgSupplier)
    {
        $this->orgSupplier = $orgSupplier;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->orgSupplier->id))->dontRelease()];
    }

    public function handle(OrgSupplier $orgSupplier): void
    {
        $stats = [
            'number_purchase_orders' => $orgSupplier->purchaseOrders()->count(),
        ];

        $stats = array_merge($stats, $this->getEnumStats(
            model:'purchase_orders',
            field: 'state',
            enum: PurchaseOrderStateEnum::class,
            models: PurchaseOrder::class,
            where: function ($q) use ($orgSupplier) {
                $q->where('parent_id', $orgSupplier->id)->where('parent_type', 'OrgSupplier');
            }
        ));

        $stats = array_merge($stats, $this->getEnumStats(
            model:'purchase_orders',
            field: 'delivery_status',
            enum: PurchaseOrderDeliveryStatusEnum::class,
            models: PurchaseOrder::class,
            where: function ($q) use ($orgSupplier) {
                $q->where('parent_id', $orgSupplier->id)->where('parent_type', 'OrgSupplier');
            }
        ));

        $orgSupplier->stats()->update($stats);
    }


}
