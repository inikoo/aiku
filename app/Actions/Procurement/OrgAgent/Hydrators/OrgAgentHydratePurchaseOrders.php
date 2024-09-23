<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 17:14:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgAgentHydratePurchaseOrders
{
    use AsAction;
    use WithEnumStats;

    private OrgAgent $orgAgent;


    public function __construct(OrgAgent $orgAgent)
    {
        $this->orgAgent = $orgAgent;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->orgAgent->id))->dontRelease()];
    }

    public function handle(OrgAgent $orgAgent): void
    {
        $stats = [
            'number_purchase_orders' => $orgAgent->purchaseOrders()->count(),
        ];

        $stats = array_merge($stats, $this->getEnumStats(
            model:'purchase_orders',
            field: 'state',
            enum: PurchaseOrderStateEnum::class,
            models: PurchaseOrder::class,
            where: function ($q) use ($orgAgent) {
                $q->where('parent_id', $orgAgent->id)->where('parent_type', 'OrgAgent');
            }
        ));

        $stats = array_merge($stats, $this->getEnumStats(
            model:'purchase_orders',
            field: 'status',
            enum: PurchaseOrderStatusEnum::class,
            models: PurchaseOrder::class,
            where: function ($q) use ($orgAgent) {
                $q->where('parent_id', $orgAgent->id)->where('parent_type', 'OrgAgent');
            }
        ));

        $orgAgent->stats()->update($stats);
    }


}
