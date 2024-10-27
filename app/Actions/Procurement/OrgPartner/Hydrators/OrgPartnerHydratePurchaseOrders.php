<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 20:16:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgPartner\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderDeliveryStatusEnum;
use App\Models\Procurement\OrgPartner;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgPartnerHydratePurchaseOrders
{
    use AsAction;
    use WithEnumStats;

    private OrgPartner $orgPartner;


    public function __construct(OrgPartner $orgPartner)
    {
        $this->orgPartner = $orgPartner;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->orgPartner->id))->dontRelease()];
    }

    public function handle(OrgPartner $orgPartner): void
    {
        $stats = [
            'number_purchase_orders' => $orgPartner->purchaseOrders()->count(),
        ];

        $stats = array_merge($stats, $this->getEnumStats(
            model:'purchase_orders',
            field: 'state',
            enum: PurchaseOrderStateEnum::class,
            models: PurchaseOrder::class,
            where: function ($q) use ($orgPartner) {
                $q->where('parent_id', $orgPartner->id)->where('parent_type', 'OrgPartner');
            }
        ));

        $stats = array_merge($stats, $this->getEnumStats(
            model:'purchase_orders',
            field: 'delivery_status',
            enum: PurchaseOrderDeliveryStatusEnum::class,
            models: PurchaseOrder::class,
            where: function ($q) use ($orgPartner) {
                $q->where('parent_id', $orgPartner->id)->where('parent_type', 'OrgPartner');
            }
        ));

        $orgPartner->stats()->update($stats);
    }


}
