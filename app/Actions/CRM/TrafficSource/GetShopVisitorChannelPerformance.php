<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Mon, 19 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\CRM\TrafficSource;

use App\Actions\Web\WebsiteVisitor\LinkAnonymousVisitorsToWebUser;
use App\Enums\CRM\TrafficSource\TrafficSourcesTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\SalesChannel\SalesChannelTypeEnum;
use App\Enums\Web\WebsiteVisitor\WebsiteVisitorChannelEnum;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GetShopVisitorChannelPerformance
{
    use AsAction;
    use WithAttributionWindow;

    private const string UNLINKED = 'unlinked';

    private const int JOURNEY_CHUNK_SIZE = 1000;

    /**
     * The channel performance table read from the website's own visits instead of the touch pivot.
     * A visit is a row of website_visitors; the ones linked to a web user at login or registration
     * form that customer's journey, and every registration, order and invoice in the period splits
     * its credit over the journey that preceded it with W-shaped attribution for a new customer and
     * U-shaped for any other (see DistributeJourneyCredit).
     *
     * Conversions whose customer has no linked visit cannot be placed in any channel and are reported
     * apart as unlinked, so the table never pretends to cover more than the tracking does.
     *
     * @return array<string, mixed>
     */
    public function handle(Shop $shop, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $context = $this->credited($shop, $from, $to);

        if (! $context) {
            return $this->emptyResult($shop, $from, $to ?? now());
        }

        ['website_id' => $websiteId, 'from' => $from, 'to' => $to, 'lookback_days' => $lookbackDays, 'tracking_started_at' => $trackingStartedAt] = $context;

        $credited     = $this->aggregate($context['conversions']);
        $visitors    = $this->visitorsByChannel($websiteId, $from, $to);
        $spend       = $this->spendByChannelType($shop, $from, $to);
        $sources     = DB::table('traffic_sources')->where('shop_id', $shop->id)->pluck('slug', 'type');

        $channelTypes = collect(array_keys($credited['buckets']))
            ->merge($visitors['by_channel']->keys())
            ->merge(array_keys($spend))
            ->unique()
            ->reject(fn ($type) => in_array($type, [DistributeJourneyCredit::DIRECT, self::UNLINKED, WebsiteVisitorChannelEnum::INTERNAL_TYPE], true));

        $channels = $channelTypes
            ->map(fn (string $type) => $this->channelRow($shop, $type, $credited['buckets'][$type] ?? [], $visitors['by_channel'][$type] ?? 0, $spend[$type] ?? null, $sources[$type] ?? null))
            ->filter(fn (array $channel) => $channel['visitors'] > 0 || $channel['spend'] > 0 || $channel['registrations'] > 0 || $channel['orders'] > 0 || $channel['revenue'] > 0)
            ->sortBy(fn (array $channel) => [-max($channel['spend'], $channel['revenue']), -$channel['revenue'], -$channel['registrations'], -$channel['visitors'], $channel['name']])
            ->values()
            ->all();

        $totals = $this->totals($channels);
        $direct = $credited['buckets'][DistributeJourneyCredit::DIRECT] ?? [];
        $lost   = $credited['buckets'][self::UNLINKED] ?? [];

        $allRevenue = $totals['revenue'] + ($direct['revenue'] ?? 0) + ($lost['revenue'] ?? 0);

        return [
            'from'                => $from->toDateString(),
            'to'                  => $to->toDateString(),
            'currency_code'       => $shop->currency->code,
            'lookback_days'       => $lookbackDays,
            'tracking_started_at' => $trackingStartedAt->toIso8601String(),
            'conversions_route'   => $this->conversionsRoute($shop),
            'unique_visitors'     => $visitors['unique'],
            'totals'              => $totals,
            'channels'            => $channels,
            'untraced'            => [
                'direct'   => [
                    'visitors'      => $visitors['by_channel'][DistributeJourneyCredit::DIRECT] ?? 0,
                    ...$this->conversionColumns($direct),
                ],
                'unlinked' => $this->conversionColumns($lost),
            ],
            'attribution'         => [
                'w_shaped_orders'    => $credited['models'][DistributeJourneyCredit::MODEL_W_SHAPED],
                'u_shaped_orders'    => $credited['models'][DistributeJourneyCredit::MODEL_U_SHAPED],
                'linked_revenue_pct' => $allRevenue > 0 ? round(($allRevenue - ($lost['revenue'] ?? 0)) / $allRevenue * 100, 1) : null,
            ],
        ];
    }

    /**
     * Every registration, order, invoice and pending order of the period with the credit its journey
     * gave each channel. The table sums it; the drill-down lists it.
     *
     * @return array{website_id: int, from: Carbon, to: Carbon, lookback_days: int, tracking_started_at: Carbon, conversions: array<int, array<string, mixed>>}|null
     */
    public function credited(Shop $shop, ?Carbon $from, ?Carbon $to): ?array
    {
        $website = $shop->website;
        $to      = $to ?? now();

        $trackingStartedAt = $website
            ? DB::table('website_visitors')->where('website_id', $website->id)->min('first_seen_at')
            : null;

        if (! $trackingStartedAt) {
            return null;
        }

        $trackingStartedAt = Carbon::parse($trackingStartedAt);
        $from              = $from && $from->isAfter($trackingStartedAt) ? $from : $trackingStartedAt->copy()->startOfDay();
        $window            = GetAttributionWindow::run($shop);
        $lookbackDays      = $window > 0 ? $window : LinkAnonymousVisitorsToWebUser::LOOKBACK_DAYS;

        return [
            'website_id'          => $website->id,
            'from'                => $from,
            'to'                  => $to,
            'lookback_days'       => $lookbackDays,
            'tracking_started_at' => $trackingStartedAt,
            'conversions'         => $this->creditConversions($website->id, $this->conversions($shop, $from, $to), $lookbackDays, $from),
        ];
    }

    /**
     * @return array<int, array{kind: string, id: int, customer_id: int, at: int, amount: float}>
     */
    private function conversions(Shop $shop, Carbon $from, Carbon $to): array
    {
        $isWebsiteTrade = fn (string $table) => fn ($query) => $query
            ->whereNull($table.'.as_organisation_id')
            ->where(fn ($channel) => $channel
                ->whereNull($table.'.sales_channel_id')
                ->orWhere('sc.type', SalesChannelTypeEnum::WEBSITE->value));

        $invoices = DB::table('invoices')
            ->leftJoin('sales_channels as sc', 'sc.id', '=', 'invoices.sales_channel_id')
            ->where('invoices.shop_id', $shop->id)
            ->where('invoices.in_process', false)
            ->tap($isWebsiteTrade('invoices'))
            ->whereBetween('invoices.date', [$from, $to])
            ->selectRaw('invoices.id, invoices.customer_id, invoices.net_amount as amount, '.self::ATTRIBUTABLE_DATE.' as at')
            ->get()
            ->map(fn ($row) => ['kind' => 'revenue'] + $this->conversionRow($row));

        $orders = DB::table('orders')
            ->leftJoin('sales_channels as sc', 'sc.id', '=', 'orders.sales_channel_id')
            ->where('orders.shop_id', $shop->id)
            ->whereNotIn('orders.state', [OrderStateEnum::CREATING, OrderStateEnum::CANCELLED])
            ->whereNull('orders.deleted_at')
            ->tap($isWebsiteTrade('orders'))
            ->whereRaw(self::ORDER_PLACED_AT.' between ? and ?', [$from, $to])
            ->selectRaw('orders.id, orders.customer_id, orders.net_amount as amount, '.self::ORDER_PLACED_AT.' as at, NOT EXISTS (SELECT 1 FROM invoices WHERE invoices.order_id = orders.id AND invoices.in_process = false) as is_pending')
            ->get();

        $placed  = $orders->map(fn ($row) => ['kind' => 'orders'] + $this->conversionRow($row));
        $pending = $orders->filter(fn ($row) => $row->is_pending)->map(fn ($row) => ['kind' => 'pending'] + $this->conversionRow($row));

        $registrations = DB::table('customers')
            ->where('shop_id', $shop->id)
            ->whereBetween('registered_at', [$from, $to])
            ->selectRaw('id, id as customer_id, 0 as amount, registered_at as at')
            ->get()
            ->map(fn ($row) => ['kind' => 'registrations'] + $this->conversionRow($row));

        return $invoices->concat($placed)->concat($pending)->concat($registrations)->values()->all();
    }

    /**
     * @return array{id: int, customer_id: int, at: int, amount: float}
     */
    private function conversionRow(object $row): array
    {
        return [
            'id'          => (int) $row->id,
            'customer_id' => (int) $row->customer_id,
            'at'          => Carbon::parse($row->at)->timestamp,
            'amount'      => (float) $row->amount,
        ];
    }

    /**
     * @param array<int, array{kind: string, id: int, customer_id: int, at: int, amount: float}> $conversions
     *
     * @return array<int, array<string, mixed>>
     */
    private function creditConversions(int $websiteId, array $conversions, int $lookbackDays, Carbon $from): array
    {
        $credited        = [];
        $lookbackSeconds = $lookbackDays * 86400;
        $distributor     = DistributeJourneyCredit::make();

        foreach (array_chunk($this->groupByCustomer($conversions), self::JOURNEY_CHUNK_SIZE, true) as $customersChunk) {
            $customerIds = array_keys($customersChunk);
            $journeys    = $this->journeys($websiteId, $customerIds, $from->copy()->subDays($lookbackDays));
            $registered  = $this->registeredAt($customerIds);
            $firstOrder  = $this->firstOrderAt($customerIds);

            foreach ($customersChunk as $customerId => $customerConversions) {
                foreach ($customerConversions as $conversion) {
                    $touches = array_values(array_filter(
                        $journeys[$customerId] ?? [],
                        fn (array $touch) => $touch['at'] <= $conversion['at'] && $touch['at'] >= $conversion['at'] - $lookbackSeconds
                    ));

                    $result = $touches === []
                        ? ['model' => null, 'credit' => [self::UNLINKED => 1.0]]
                        : $distributor->handle(
                            $touches,
                            $conversion['kind'] !== 'registrations' && $this->isNewCustomer($conversion['at'], $registered[$customerId] ?? null, $firstOrder[$customerId] ?? null, $lookbackSeconds),
                            $registered[$customerId] ?? null
                        );

                    $credited[] = $conversion + $result;
                }
            }
        }

        return $credited;
    }

    /**
     * @param array<int, array<string, mixed>> $conversions
     *
     * @return array{buckets: array<string, array<string, float>>, models: array<string, int>}
     */
    private function aggregate(array $conversions): array
    {
        $buckets = [];
        $models  = [DistributeJourneyCredit::MODEL_W_SHAPED => 0, DistributeJourneyCredit::MODEL_U_SHAPED => 0];

        foreach ($conversions as $conversion) {
            if ($conversion['kind'] === 'orders' && $conversion['model']) {
                $models[$conversion['model']]++;
            }

            $isMoney = in_array($conversion['kind'], ['revenue', 'pending'], true);

            foreach ($conversion['credit'] as $channel => $share) {
                $buckets[$channel][$conversion['kind']] = ($buckets[$channel][$conversion['kind']] ?? 0.0)
                    + ($isMoney ? $conversion['amount'] : 1.0) * $share;
            }
        }

        return ['buckets' => $buckets, 'models' => $models];
    }

    /**
     * @param array<int, array{kind: string, id: int, customer_id: int, at: int, amount: float}> $conversions
     *
     * @return array<int, array<int, array{kind: string, id: int, customer_id: int, at: int, amount: float}>>
     */
    private function groupByCustomer(array $conversions): array
    {
        $grouped = [];
        foreach ($conversions as $conversion) {
            $grouped[$conversion['customer_id']][] = $conversion;
        }

        return $grouped;
    }

    private function isNewCustomer(int $conversionAt, ?int $registeredAt, ?int $firstOrderAt, int $lookbackSeconds): bool
    {
        return $registeredAt !== null
            && $registeredAt <= $conversionAt
            && $registeredAt >= $conversionAt - $lookbackSeconds
            && ($firstOrderAt === null || $conversionAt <= $firstOrderAt);
    }

    /**
     * @param array<int, int> $customerIds
     *
     * @return array<int, array<int, array{channel: string|null, at: int}>>
     */
    private function journeys(int $websiteId, array $customerIds, Carbon $since): array
    {
        $journeys = [];

        DB::table('website_visitors as v')
            ->join('web_users as u', 'u.id', '=', 'v.web_user_id')
            ->where('v.website_id', $websiteId)
            ->whereIn('u.customer_id', $customerIds)
            ->where('v.first_seen_at', '>=', $since)
            ->orderBy('v.first_seen_at')
            ->select('u.customer_id', 'v.first_seen_at', 'v.traffic_source_type')
            ->cursor()
            ->each(function ($visit) use (&$journeys) {
                $journeys[$visit->customer_id][] = [
                    'channel' => $visit->traffic_source_type,
                    'at'      => Carbon::parse($visit->first_seen_at)->timestamp,
                ];
            });

        return $journeys;
    }

    /**
     * @param array<int, int> $customerIds
     *
     * @return array<int, int>
     */
    private function registeredAt(array $customerIds): array
    {
        return DB::table('customers')
            ->whereIn('id', $customerIds)
            ->whereNotNull('registered_at')
            ->pluck('registered_at', 'id')
            ->map(fn ($registeredAt) => Carbon::parse($registeredAt)->timestamp)
            ->all();
    }

    /**
     * @param array<int, int> $customerIds
     *
     * @return array<int, int>
     */
    private function firstOrderAt(array $customerIds): array
    {
        return DB::table('orders')
            ->whereIn('customer_id', $customerIds)
            ->whereNotIn('state', [OrderStateEnum::CREATING, OrderStateEnum::CANCELLED])
            ->whereNull('deleted_at')
            ->groupBy('customer_id')
            ->selectRaw('customer_id, MIN(COALESCE(submitted_at, date)) as first_order_at')
            ->pluck('first_order_at', 'customer_id')
            ->map(fn ($firstOrderAt) => Carbon::parse($firstOrderAt)->timestamp)
            ->all();
    }

    /**
     * Unique visitors: one browser counts once per channel however many sessions it opened, and once
     * overall in the total.
     *
     * @return array{by_channel: Collection<string, int>, unique: int}
     */
    private function visitorsByChannel(int $websiteId, Carbon $from, Carbon $to): array
    {
        $base = fn () => DB::table('website_visitors')
            ->where('website_id', $websiteId)
            ->whereBetween('first_seen_at', [$from, $to]);

        return [
            'by_channel' => $base()
                ->whereNotNull('traffic_source_type')
                ->groupBy('traffic_source_type')
                ->selectRaw('traffic_source_type, COUNT(DISTINCT COALESCE(visitor_uuid::text, visitor_hash)) as visitors')
                ->pluck('visitors', 'traffic_source_type')
                ->map(fn ($visitors) => (int) $visitors),
            'unique'     => (int) $base()
                ->where(fn ($query) => $query->whereNull('traffic_source_type')->orWhere('traffic_source_type', '!=', WebsiteVisitorChannelEnum::INTERNAL_TYPE))
                ->distinct()
                ->count(DB::raw('COALESCE(visitor_uuid::text, visitor_hash)')),
        ];
    }

    /**
     * What each channel cost in the period: the recorded ad spend, plus the estimated cost of sending
     * the shop's emails for the three email channels, as the overview does.
     *
     * @return array<string, array{amount: float, estimated: bool}>
     */
    private function spendByChannelType(Shop $shop, Carbon $from, Carbon $to): array
    {
        $spend = DB::table('traffic_source_costs as c')
            ->join('traffic_sources as ts', 'ts.id', '=', 'c.traffic_source_id')
            ->where('c.shop_id', $shop->id)
            ->whereBetween('c.date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('ts.type')
            ->selectRaw('ts.type, SUM(c.amount) as amount')
            ->pluck('amount', 'type')
            ->map(fn ($amount) => ['amount' => (float) $amount, 'estimated' => false])
            ->all();

        $costPerEmail   = GetEstimatedEmailCost::make()->costPerEmail($shop->currency);
        $mailshotTotals = GetEstimatedEmailCost::totalsByType([$shop->id], $from, $to);
        $emailCosts     = [
            TrafficSourcesTypeEnum::NEWSLETTER->value         => GetEstimatedEmailCost::forChannel($mailshotTotals, TrafficSourcesTypeEnum::NEWSLETTER, $costPerEmail)['cost'],
            TrafficSourcesTypeEnum::MARKETING_MAILSHOT->value => GetEstimatedEmailCost::forChannel($mailshotTotals, TrafficSourcesTypeEnum::MARKETING_MAILSHOT, $costPerEmail)['cost'],
            TrafficSourcesTypeEnum::EMAIL_AUTOMATED->value    => GetEstimatedEmailCost::automated([$shop->id], $from, $to, $shop->currency, $costPerEmail),
        ];

        foreach ($emailCosts as $type => $cost) {
            if ($cost > 0) {
                $spend[$type] = ['amount' => ($spend[$type]['amount'] ?? 0.0) + $cost, 'estimated' => true];
            }
        }

        return $spend;
    }

    /**
     * @param array<string, float>                      $credited
     * @param array{amount: float, estimated: bool}|null $spend
     *
     * @return array<string, mixed>
     */
    private function channelRow(Shop $shop, string $type, array $credited, int $visitors, ?array $spend, ?string $slug): array
    {
        $group = TrafficSourcesTypeEnum::tryFrom($type)?->group();

        $channel = [
            'type'               => $type,
            'name'               => TrafficSourcesTypeEnum::labels()[$type] ?? WebsiteVisitorChannelEnum::typeLabel($type) ?? $type,
            'group'              => $group['key'] ?? 'other',
            'group_label'        => $group['label'] ?? __('Other'),
            'group_position'     => $group['position'] ?? 9,
            'route'              => $slug ? [
                'name'       => 'grp.org.shops.show.marketing.traffic_sources.show',
                'parameters' => ['organisation' => $shop->organisation->slug, 'shop' => $shop->slug, 'trafficSource' => $slug],
            ] : null,
            'visitors'           => $visitors,
            'spend'              => round($spend['amount'] ?? 0, 2),
            'spend_is_estimated' => $spend['estimated'] ?? false,
        ] + $this->conversionColumns($credited);

        return $channel + [
            'roas' => ($channel['spend'] > 0 && ($channel['revenue'] > 0 || $channel['pending'] <= 0))
                ? round($channel['revenue'] / $channel['spend'], 2)
                : null,
        ];
    }

    /**
     * @param array<string, float> $credited
     *
     * @return array{registrations: float, orders: float, revenue: float, pending: float}
     */
    private function conversionColumns(array $credited): array
    {
        return [
            'registrations' => round($credited['registrations'] ?? 0, 2),
            'orders'        => round($credited['orders'] ?? 0, 2),
            'revenue'       => round($credited['revenue'] ?? 0, 2),
            'pending'       => round($credited['pending'] ?? 0, 2),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $channels
     *
     * @return array<string, float|int|null>
     */
    private function totals(array $channels): array
    {
        $spend         = round(array_sum(array_column($channels, 'spend')), 2);
        $revenue       = round(array_sum(array_column($channels, 'revenue')), 2);
        $pending       = round(array_sum(array_column($channels, 'pending')), 2);
        $registrations = round(array_sum(array_column($channels, 'registrations')), 2);

        return [
            'spend'         => $spend,
            'revenue'       => $revenue,
            'pending'       => $pending,
            'registrations' => $registrations,
            'orders'        => round(array_sum(array_column($channels, 'orders')), 2),
            'roas'          => ($spend > 0 && ($revenue > 0 || $pending <= 0)) ? round($revenue / $spend, 2) : null,
            'cac'           => ($spend > 0 && $registrations > 0) ? round($spend / $registrations, 2) : null,
        ];
    }

    /**
     * @return array{name: string, parameters: array<string, string>}
     */
    private function conversionsRoute(Shop $shop): array
    {
        return [
            'name'       => 'grp.org.shops.show.marketing.visitor_channels.conversions',
            'parameters' => ['organisation' => $shop->organisation->slug, 'shop' => $shop->slug],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyResult(Shop $shop, ?Carbon $from, Carbon $to): array
    {
        return [
            'from'                => $from?->toDateString(),
            'to'                  => $to->toDateString(),
            'currency_code'       => $shop->currency->code,
            'lookback_days'       => GetAttributionWindow::run($shop),
            'tracking_started_at' => null,
            'conversions_route'   => $this->conversionsRoute($shop),
            'unique_visitors'     => 0,
            'totals'              => $this->totals([]),
            'channels'            => [],
            'untraced'            => [
                'direct'   => ['visitors' => 0] + $this->conversionColumns([]),
                'unlinked' => $this->conversionColumns([]),
            ],
            'attribution'         => ['w_shaped_orders' => 0, 'u_shaped_orders' => 0, 'linked_revenue_pct' => null],
        ];
    }
}
