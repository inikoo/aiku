<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Hydrators;

use Illuminate\Support\Facades\DB;

trait WithLeadTimeMeasurement
{
    /**
     * Days from submitting a purchase order to its delivery landing, averaged over the orders of
     * the last 12 months. A supplier ordered from once a year has fewer than 3 of those, so its
     * last 3 orders of any age are used instead of falling back to the hand-typed estimate. Each order
     * counts once, landing with its first delivery, however many deliveries or lines it was split into.
     *
     * @return array{measured_lead_time_days: int|null, lead_time_samples: int}
     */
    protected function measureLeadTime(string $column, int $id): array
    {
        $days = DB::table('purchase_order_transactions')
            ->join('purchase_orders', 'purchase_orders.id', 'purchase_order_transactions.purchase_order_id')
            ->join('purchase_order_stock_delivery', 'purchase_order_stock_delivery.purchase_order_id', 'purchase_orders.id')
            ->join('stock_deliveries', 'stock_deliveries.id', 'purchase_order_stock_delivery.stock_delivery_id')
            ->where('purchase_order_transactions.'.$column, $id)
            ->whereNull('purchase_orders.deleted_at')
            ->whereNotNull('purchase_orders.submitted_at')
            ->whereRaw('coalesce(stock_deliveries.booked_in_at, stock_deliveries.placed_at) is not null')
            ->groupBy('purchase_orders.id', 'purchase_orders.submitted_at')
            ->orderByDesc('purchase_orders.submitted_at')
            ->orderByDesc('purchase_orders.id')
            ->selectRaw('extract(epoch from min(coalesce(stock_deliveries.booked_in_at, stock_deliveries.placed_at)) - purchase_orders.submitted_at) / 86400 as days')
            ->selectRaw("purchase_orders.submitted_at >= now() - interval '12 months' as is_recent")
            ->get()
            ->filter(fn (object $row, int $index) => $row->is_recent || $index < 3)
            ->pluck('days');

        $samples = $days->count();

        return [
            'measured_lead_time_days' => $samples >= 3 ? max(1, (int) round($days->avg())) : null,
            'lead_time_samples'       => $samples,
        ];
    }
}
