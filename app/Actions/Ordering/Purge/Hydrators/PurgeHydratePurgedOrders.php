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
use App\Enums\Ordering\PurgedOrder\PurgedOrderStateEnum;
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
            ->where('status', PurgedOrderStateEnum::PURGED)
            ->count();

        $estimatedNumberTransactions = $purge->purgedOrders()
            ->with('order.transactions')
            ->get()
            ->sum(function ($purgedOrder) {
                return $purgedOrder->order->transactions->count();
            });

        $purgedTransactions = $purge->purgedOrders()
            ->where('status', PurgedOrderStateEnum::PURGED)
            ->with('order.transactions')
            ->get()
            ->sum(function ($purgedOrder) {
                return $purgedOrder->order->transactions->count();
            });

        $estimatedPurgedOrderAmounts = $purge->purgedOrders()
            ->get();

        $estimatedpPurgedAmount = $estimatedPurgedOrderAmounts->sum(function ($purgedOrder) {
            return $purgedOrder->order->net_amount ?? 0;
        });

        $estimatedPurgedOrgAmount = $estimatedPurgedOrderAmounts->sum(function ($purgedOrder) {
            return $purgedOrder->order->org_net_amount ?? 0;
        });

        $estimatedPurgedGrpAmount = $estimatedPurgedOrderAmounts->sum(function ($purgedOrder) {
            return $purgedOrder->order->grp_net_amount ?? 0;
        });

        $purgedOrderAmounts = $purge->purgedOrders()
            ->where('status', PurgedOrderStateEnum::PURGED)
            ->get();

        $purgedAmount = $purgedOrderAmounts->sum(function ($purgedOrder) {
            return $purgedOrder->order->net_amount ?? 0;
        });

        $purgedOrgAmount = $purgedOrderAmounts->sum(function ($purgedOrder) {
            return $purgedOrder->order->org_net_amount ?? 0;
        });

        $purgedGrpAmount = $purgedOrderAmounts->sum(function ($purgedOrder) {
            return $purgedOrder->order->grp_net_amount ?? 0;
        });

        $stats = [
            'estimated_number_orders'          => $purge->purgedOrders()->count(),
            'estimated_number_transactions'    => $estimatedNumberTransactions,
            'number_purged_orders'             => $purgedOrders,
            'number_purged_transactions'       => $purgedTransactions,
            'estimated_amount'                 => $estimatedpPurgedAmount,
            'estimated_org_amount'             => $estimatedPurgedOrgAmount,
            'estimated_grp_amount'             => $estimatedPurgedGrpAmount,
            'purged_amount'                    => $purgedAmount,
            'purged_org_amount'                => $purgedOrgAmount,
            'purged_grp_amount'                => $purgedGrpAmount
        ];

        $stats = array_merge($stats, $this->getEnumStats(
            model:'purged_orders',
            field: 'status',
            enum: PurgedOrderStateEnum::class,
            models: PurgedOrder::class,
            where: function ($q) use ($purge) {
                $q->where('purge_id', $purge->id);
            }
        ));
        $purge->stats()->update($stats);
    }
}
