<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 May 2024 22:34:50 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Models\SysAdmin\Group;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydratePurchaseOrders
{
    use AsAction;
    use WithEnumStats;

    private Group $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->group->id))->dontRelease()];
    }

    public function handle(Group $group): void
    {
        $stats = [
            'number_purchase_orders' => $group->purchaseOrders->count()
        ];


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'purchase_orders',
                field: 'status',
                enum: PurchaseOrderStatusEnum::class,
                models: PurchaseOrder::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'purchase_orders',
                field: 'state',
                enum: PurchaseOrderStateEnum::class,
                models: PurchaseOrder::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $group->supplyChainStats()->update($stats);
    }
}
