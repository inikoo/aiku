<?php
/*
 * author Arya Permana - Kirin
 * created on 04-11-2024-10h-28m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Ordering\Purge\Hydrators;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Enums\Ordering\Purge\PurgedOrderStatusEnum;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Ordering\Purge;
use App\Models\Ordering\PurgedOrder;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class PurgeHydratePurgedOrders extends HydrateModel
{
    use AsAction;
    use WithEnumStats;

    private Purge $purge;
    public function __construct(Purge $purge)
    {
        $this->purge = $purge;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->purge->id))->dontRelease()];
    }

    public function handle(Purge $purge): void
    {
        $purgedOrders = $purge->purgedOrders()
            ->where('status', PurgedOrderStatusEnum::PURGED)
            ->count();
        
        $estimatedNumberTransactions = $purge->purgedOrders()
            ->with('order.transactions')
            ->get()
            ->sum(function ($purgedOrder) {
                return $purgedOrder->order->transactions->count();
            });

        $purgedTransactions = $purge->purgedOrders()
            ->where('status', PurgedOrderStatusEnum::PURGED)
            ->with('order.transactions')
            ->get()
            ->sum(function ($purgedOrder) {
                return $purgedOrder->order->transactions->count();
            });

        $purgedAmount = $purge->purgedOrders()
            ->where('status', PurgedOrderStatusEnum::PURGED)
            ->sum('order.net_amount');

        $purgedOrgAmount = $purge->purgedOrders()
            ->where('status', PurgedOrderStatusEnum::PURGED)
            ->sum('order.org_net_amount');

        $purgedGrpAmount = $purge->purgedOrders()
            ->where('status', PurgedOrderStatusEnum::PURGED)
            ->sum('order.grp_net_amount');

        $stats = [
            'estimated_number_orders'          => $purge->purgedOrders()->count(),
            'estimated_number_transactions'    => $estimatedNumberTransactions,
            'number_purged_orders'             => $purgedOrders,
            'number_purged_transactions'       => $purgedTransactions,
            'estimated_amount'                 => $purge->purgedOrders()->sum('order.net_amount'),
            'estimated_org_amount'             => $purge->purgedOrders()->sum('order.org_net_amount'),
            'estimated_grp_amount'             => $purge->purgedOrders()->sum('order.grp_net_amount'),
            'purged_amount'                    => $purgedAmount,
            'purged_org_amount'                => $purgedOrgAmount,
            'purged_grp_amount'                => $purgedGrpAmount
        ];

        $stats = array_merge($stats, $this->getEnumStats(
            model:'purged_orders',
            field: 'status',
            enum: PurgedOrderStatusEnum::class,
            models: PurgedOrder::class,
            where: function ($q) use ($purge) {
                $q->where('purge_id', $purge->id);
            }
        ));
        $purge->stats()->update($stats);
    }
}
