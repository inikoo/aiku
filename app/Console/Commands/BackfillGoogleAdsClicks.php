<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Fri, 23 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Console\Commands;

use App\Actions\CRM\TrafficSource\ParseGoogleAdsClick;
use App\Enums\CRM\TrafficSource\TrafficSourcesTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Ordering\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Copies the Google ad clicks still in the click log onto the customers who made them and the orders
 * they led to, before the log's prune takes them. A click is credited to a customer only when it
 * resolves to exactly one, directly or through the browser that later signed in.
 */
class BackfillGoogleAdsClicks extends Command
{
    protected $signature = 'traffic-source:backfill-google-ads-clicks
                           {--shop= : Only this shop id}
                           {--dry-run : Report what would be written without writing}';

    protected $description = 'Copy logged Google ad clicks onto their customers and orders';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (!$dryRun) {
            $typed = DB::table('traffic_source_clicks')
                ->where('type', TrafficSourcesTypeEnum::GOOGLE_ADS->value)
                ->whereNotNull('click_id')
                ->whereNull('click_id_type')
                ->update(['click_id_type' => 'gclid']);

            $this->info("Click id type set on {$typed} logged click(s).");
        }

        $shopIds = DB::table('traffic_source_clicks')
            ->where('type', TrafficSourcesTypeEnum::GOOGLE_ADS->value)
            ->when($this->option('shop'), fn ($query, $shopId) => $query->where('shop_id', (int) $shopId))
            ->distinct()
            ->pluck('shop_id');

        $customersUpdated = 0;
        $ordersUpdated    = 0;

        foreach ($shopIds as $shopId) {
            $clicksByCustomer = $this->resolvedClicks($shopId)->groupBy('customer_id');

            foreach ($clicksByCustomer as $customerId => $clicks) {
                $customersUpdated += $this->backfillCustomer($customerId, $clicks->sortByDesc('clicked_at')->first(), $dryRun);
                $ordersUpdated    += $this->backfillOrders($customerId, $clicks, $dryRun);
            }
        }

        $this->info(($dryRun ? '[dry run] ' : '')."Customers: {$customersUpdated}, orders: {$ordersUpdated}.");

        return Command::SUCCESS;
    }

    /**
     * @return Collection<int, object{click_id: string, click_id_type: string, clicked_at: Carbon, customer_id: int}>
     */
    private function resolvedClicks(int $shopId): Collection
    {
        $rows = DB::select(
            "
            WITH clicks AS (
                SELECT DISTINCT ON (click_id) click_id, COALESCE(click_id_type, 'gclid') AS click_id_type, created_at, customer_id, session_id
                FROM traffic_source_clicks
                WHERE shop_id = ? AND type = ? AND NOT is_bot AND click_id IS NOT NULL
                  AND created_at >= now() - (? * interval '1 day')
                ORDER BY click_id, created_at
            ),
            resolved AS (
                SELECT click_id, click_id_type, created_at, customer_id
                FROM clicks
                WHERE customer_id IS NOT NULL
                UNION
                SELECT c.click_id, c.click_id_type, c.created_at, web_users.customer_id
                FROM clicks c
                JOIN website_visitors v ON v.session_id = c.session_id
                JOIN website_visitors sibling ON sibling.visitor_hash = v.visitor_hash AND sibling.website_id = v.website_id
                JOIN web_users ON web_users.id = sibling.web_user_id
                WHERE c.customer_id IS NULL AND c.session_id IS NOT NULL
            )
            SELECT click_id, click_id_type, created_at, MIN(customer_id) AS customer_id
            FROM resolved
            GROUP BY click_id, click_id_type, created_at
            HAVING COUNT(DISTINCT customer_id) = 1
            ",
            [$shopId, TrafficSourcesTypeEnum::GOOGLE_ADS->value, ParseGoogleAdsClick::WINDOW_DAYS]
        );

        return collect($rows)->map(fn (object $row) => (object) [
            'click_id'      => $row->click_id,
            'click_id_type' => $row->click_id_type,
            'clicked_at'    => Carbon::parse($row->created_at),
            'customer_id'   => (int) $row->customer_id,
        ]);
    }

    private function backfillCustomer(int $customerId, object $latestClick, bool $dryRun): int
    {
        $customer = Customer::find($customerId);

        if (!$customer || ($customer->google_ads_clicked_at && $customer->google_ads_clicked_at->gte($latestClick->clicked_at))) {
            return 0;
        }

        if (!$dryRun) {
            $customer->updateQuietly($this->clickColumns($latestClick));
        }

        return 1;
    }

    /**
     * @param Collection<int, object{click_id: string, click_id_type: string, clicked_at: Carbon}> $clicks
     */
    private function backfillOrders(int $customerId, Collection $clicks, bool $dryRun): int
    {
        $orders = Order::where('customer_id', $customerId)
            ->whereNull('google_ads_click_id')
            ->where('submitted_at', '>=', $clicks->min('clicked_at'))
            ->get(['id', 'submitted_at']);

        $updated = 0;

        foreach ($orders as $order) {
            $click = $clicks
                ->filter(fn (object $click) => ParseGoogleAdsClick::isWithinWindow($click->clicked_at, $order->submitted_at))
                ->sortByDesc('clicked_at')
                ->first();

            if (!$click) {
                continue;
            }

            if (!$dryRun) {
                $order->updateQuietly($this->clickColumns($click));
            }

            $updated++;
        }

        return $updated;
    }

    /**
     * @return array{google_ads_click_id: string, google_ads_click_id_type: string, google_ads_clicked_at: Carbon, google_ads_ad_user_data_consent: null}
     */
    private function clickColumns(object $click): array
    {
        return [
            'google_ads_click_id'             => $click->click_id,
            'google_ads_click_id_type'        => $click->click_id_type,
            'google_ads_clicked_at'           => $click->clicked_at,
            'google_ads_ad_user_data_consent' => null,
        ];
    }
}
