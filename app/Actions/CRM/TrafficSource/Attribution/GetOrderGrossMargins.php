<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Thu, 22 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\CRM\TrafficSource\Attribution;

use App\Models\Catalogue\Shop;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Gross margin of orders from their product lines: the line's net amount less the quantity times
 * the current cost of its master product. The cost is in the group currency, so it is converted
 * with the line's own rate (net_amount / grp_net_amount). Lines without a cost are returned apart
 * as revenue, for the caller to apply the shop's average margin. Charges and shipping are left out.
 */
class GetOrderGrossMargins
{
    use AsAction;

    private const string COSTED = 'master_assets.effective_cost > 0 AND transactions.grp_net_amount > 0 AND transactions.net_amount > 0';

    private const string LINE_MARGIN = 'transactions.net_amount * (1 - transactions.quantity_ordered * master_assets.effective_cost / transactions.grp_net_amount)';

    /**
     * @param  array<int, int>  $orderIds
     *
     * @return array<int, float> gross margin by order id
     */
    public function handle(Shop $shop, array $orderIds, float $averageMargin): array
    {
        $margins = [];

        foreach (array_chunk($orderIds, 5000) as $chunk) {
            self::lines($shop)
                ->whereIn('transactions.order_id', $chunk)
                ->get()
                ->each(function (object $order) use (&$margins, $averageMargin) {
                    $margins[$order->order_id] = (float) $order->costed_margin + (float) $order->uncosted_revenue * $averageMargin;
                });
        }

        return $margins;
    }

    public static function lines(Shop $shop): Builder
    {
        return self::productLines($shop)
            ->groupBy('transactions.order_id')
            ->select('transactions.order_id')
            ->selectRaw('COALESCE(SUM('.self::LINE_MARGIN.') FILTER (WHERE '.self::COSTED.'), 0) AS costed_margin')
            ->selectRaw('COALESCE(SUM(transactions.net_amount) FILTER (WHERE NOT ('.self::COSTED.')), 0) AS uncosted_revenue');
    }

    /**
     * The margin of the lines with a cost over their revenue, and the share of product revenue
     * those lines carry.
     *
     * @return array{average_margin: float|null, cost_coverage: float|null}
     */
    public static function averageMargin(Shop $shop, Builder $orders): array
    {
        $totals = self::productLines($shop)
            ->whereIn('transactions.order_id', $orders)
            ->selectRaw('SUM('.self::LINE_MARGIN.') FILTER (WHERE '.self::COSTED.') AS costed_margin')
            ->selectRaw('SUM(transactions.net_amount) FILTER (WHERE '.self::COSTED.') AS costed_revenue')
            ->selectRaw('SUM(transactions.net_amount) AS revenue')
            ->first();

        $costedRevenue = (float) $totals->costed_revenue;
        $revenue       = (float) $totals->revenue;

        return [
            'average_margin' => $costedRevenue > 0 ? round((float) $totals->costed_margin / $costedRevenue, 4) : null,
            'cost_coverage'  => $revenue > 0 ? round($costedRevenue / $revenue, 4) : null,
        ];
    }

    private static function productLines(Shop $shop): Builder
    {
        return DB::table('transactions')
            ->leftJoin('products', 'products.id', '=', 'transactions.model_id')
            ->leftJoin('master_assets', 'master_assets.id', '=', 'products.master_product_id')
            ->where('transactions.shop_id', $shop->id)
            ->where('transactions.model_type', 'Product')
            ->whereNull('transactions.deleted_at');
    }
}
