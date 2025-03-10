<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 May 2024 19:26:53 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Accounting\Invoice\InvoicePayStatusEnum;
use App\Enums\Ordering\Order\OrderHandingTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Order\OrderStatusEnum;
use App\Models\SysAdmin\Group;
use App\Models\Ordering\Order;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateOrders
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
            'number_orders' => DB::table('orders')->where('group_id', $group->id)->count(),
            'number_orders_stat_submited_paid' => DB::table('orders')
                ->leftJoin('invoices', 'orders.id', '=', 'invoices.order_id')
                ->where('orders.group_id', $group->id)
                ->whereNull('orders.deleted_at')
                ->where('orders.state', OrderStateEnum::SUBMITTED->value)
                ->where('invoices.pay_status', InvoicePayStatusEnum::PAID->value)
                ->count(),
            'number_orders_stat_submited_unpaid' => DB::table('orders')
                ->leftJoin('invoices', 'orders.id', '=', 'invoices.order_id')
                ->where('orders.group_id', $group->id)
                ->whereNull('orders.deleted_at')
                ->where('orders.state', OrderStateEnum::SUBMITTED->value)
                ->where('invoices.pay_status', InvoicePayStatusEnum::UNPAID->value)
                ->count(),
            'number_orders_state_dispatched_today' => DB::table('orders')
                ->where('orders.group_id', $group->id)
                ->whereNull('orders.deleted_at')
                ->where('orders.state', OrderStateEnum::DISPATCHED->value)
                ->whereDate('orders.date', now()->toDateString())
                ->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'orders',
                field: 'state',
                enum: OrderStateEnum::class,
                models: Order::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'orders',
                field: 'status',
                enum: OrderStatusEnum::class,
                models: Order::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'orders',
                field: 'handing_type',
                enum: OrderHandingTypeEnum::class,
                models: Order::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        foreach (OrderStateEnum::cases() as $case) {
            $stats['orders_net_org_amount_state_' . $case->snake()] = DB::table('orders')
                ->where('group_id', $group->id)
                ->whereNull('deleted_at')
                ->where('state', $case->value)
                ->sum('org_net_amount');

            $stats['orders_net_grp_amount_state_' . $case->snake()] = DB::table('orders')
                ->where('group_id', $group->id)
                ->whereNull('deleted_at')
                ->where('state', $case->value)
                ->sum('grp_net_amount');

            $stats['orders_net_amount_state_' . $case->snake()] = DB::table('orders')
                ->where('group_id', $group->id)
                ->whereNull('deleted_at')
                ->where('state', $case->value)
                ->sum('net_amount');
        }

        foreach (OrderStatusEnum::cases() as $case) {
            $stats['orders_net_org_amount_status_' . $case->snake()] = DB::table('orders')
                ->where('group_id', $group->id)
                ->whereNull('deleted_at')
                ->where('status', $case->value)
                ->sum('org_net_amount');

            $stats['orders_net_grp_amount_status_' . $case->snake()] = DB::table('orders')
                ->where('group_id', $group->id)
                ->whereNull('deleted_at')
                ->where('status', $case->value)
                ->sum('grp_net_amount');

            $stats['orders_net_amount_status_' . $case->snake()] = DB::table('orders')
                ->where('group_id', $group->id)
                ->whereNull('deleted_at')
                ->where('status', $case->value)
                ->sum('net_amount');
        }

        $group->orderingStats()->update($stats);
    }


}
