<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:58:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Accounting\Invoice\InvoicePayStatusEnum;
use App\Enums\Ordering\Order\OrderHandingTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Order\OrderStatusEnum;
use App\Models\Catalogue\Shop;
use App\Models\Ordering\Order;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateOrders
{
    use AsAction;
    use WithEnumStats;

    private Shop $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->shop->id))->dontRelease()];
    }

    public function handle(Shop $shop): void
    {
        $stats = [
            'number_orders' => DB::table('orders')->where('shop_id', $shop->id)->count(),
            'number_orders_stat_submited_paid' => DB::table('orders')
                ->leftJoin('invoices', 'orders.id', '=', 'invoices.order_id')
                ->where('orders.shop_id', $shop->id)
                ->whereNull('orders.deleted_at')
                ->where('orders.state', OrderStateEnum::SUBMITTED->value)
                ->where('invoices.pay_status', InvoicePayStatusEnum::PAID->value)
                ->count(),
            'number_orders_stat_submited_unpaid' => DB::table('orders')
                ->leftJoin('invoices', 'orders.id', '=', 'invoices.order_id')
                ->where('orders.shop_id', $shop->id)
                ->whereNull('orders.deleted_at')
                ->where('orders.state', OrderStateEnum::SUBMITTED->value)
                ->where('invoices.pay_status', InvoicePayStatusEnum::UNPAID->value)
                ->count(),
            'number_orders_state_dispatched_today' => DB::table('orders')
                ->where('orders.shop_id', $shop->id)
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
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
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
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
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
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );

        foreach (OrderStateEnum::cases() as $case) {
            $stats['orders_net_org_amount_state_' . $case->snake()] = DB::table('orders')
                ->where('shop_id', $shop->id)
                ->whereNull('deleted_at')
                ->where('state', $case->value)
                ->sum('org_net_amount');

            $stats['orders_net_grp_amount_state_' . $case->snake()] = DB::table('orders')
                ->where('shop_id', $shop->id)
                ->whereNull('deleted_at')
                ->where('state', $case->value)
                ->sum('grp_net_amount');

            $stats['orders_net_amount_state_' . $case->snake()] = DB::table('orders')
                ->where('shop_id', $shop->id)
                ->whereNull('deleted_at')
                ->where('state', $case->value)
                ->sum('net_amount');
        }

        foreach (OrderStatusEnum::cases() as $case) {
            $stats['orders_net_org_amount_status_' . $case->snake()] = DB::table('orders')
                ->where('shop_id', $shop->id)
                ->whereNull('deleted_at')
                ->where('status', $case->value)
                ->sum('org_net_amount');

            $stats['orders_net_grp_amount_status_' . $case->snake()] = DB::table('orders')
                ->where('shop_id', $shop->id)
                ->whereNull('deleted_at')
                ->where('status', $case->value)
                ->sum('grp_net_amount');

            $stats['orders_net_amount_status_' . $case->snake()] = DB::table('orders')
                ->where('shop_id', $shop->id)
                ->whereNull('deleted_at')
                ->where('status', $case->value)
                ->sum('net_amount');
        }


        $shop->orderingStats()->update($stats);
    }

}
