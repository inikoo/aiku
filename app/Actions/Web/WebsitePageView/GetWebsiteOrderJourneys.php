<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Fri, 16 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\WebsitePageView;

use App\Enums\Web\WebsiteConversionEvent\WebsiteConversionEventTypeEnum;
use App\Enums\Web\WebsiteVisitor\WebsiteVisitorChannelEnum;
use App\Models\Web\Website;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * The latest orders placed on the website, each with its customer and the traffic source of every
 * visit that led to it, so the sources read as a path: Email, then Direct, then Direct. The orders
 * can be narrowed by the sources of their visits, how many visits they took, their amount, a page a
 * visit landed on and the order number or customer name.
 */
class GetWebsiteOrderJourneys
{
    use AsAction;
    use WithOrderVisits;

    public const ORDERS        = 50;
    public const DAY_OPTIONS   = [30, 90, 180];
    public const VISIT_OPTIONS = ['1', '2-3', '4+'];

    /**
     * @param array<string, mixed> $input
     *
     * @return array{sources: array<int, string>, visits: string|null, min: float|null, max: float|null, landing: string|null, search: string|null}
     */
    public static function normaliseFilters(array $input): array
    {
        $sources = $input['sources'] ?? [];
        if (is_string($sources)) {
            $sources = explode(',', $sources);
        }

        $amount = fn (mixed $value) => is_numeric($value) ? (float) $value : null;
        $text   = fn (mixed $value) => is_string($value) && trim($value) !== '' ? mb_substr(trim($value), 0, 255) : null;

        return [
            'sources' => is_array($sources) ? array_values(array_unique(array_filter(array_map(fn ($source) => is_string($source) ? trim($source) : '', $sources)))) : [],
            'visits'  => in_array($input['visits'] ?? null, self::VISIT_OPTIONS, true) ? $input['visits'] : null,
            'min'     => $amount($input['min'] ?? null),
            'max'     => $amount($input['max'] ?? null),
            'landing' => $text($input['landing'] ?? null),
            'search'  => $text($input['search'] ?? null),
        ];
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array{total: int, matching: int, page: int, per_page: int, currency_code: string|null, source_options: array<int, array{value: string, label: string}>, orders: array<int, array{purchase_id: int, reference: string, submitted_at: string, net_amount: float, currency_code: string|null, customer: array{name: string|null, route: array{name: string, parameters: array<int, string>}}, order_route: array{name: string, parameters: array<int, string>}, visits: int, sources: array<int, array{type: string|null, label: string, reference: string|null}>}>}
     */
    public function handle(Website $website, Carbon $from, Carbon $to, array $filters = [], int $page = 1): array
    {
        $filters = self::normaliseFilters($filters);
        $page    = max(1, $page);

        $summary = $this->summary($website, $from, $to, $filters, $page);

        $lastPage = max(1, (int) ceil($summary->matching / self::ORDERS));
        if ($page > $lastPage) {
            $page    = $lastPage;
            $summary = $this->summary($website, $from, $to, $filters, $page);
        }

        $sourceOptions = collect(json_decode($summary->source_types ?? '[]', true))
            ->map(fn (string $type) => ['value' => $type, 'label' => WebsiteVisitorChannelEnum::typeLabel($type) ?? $type])
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        return [
            'total'          => (int) $summary->total,
            'matching'       => (int) $summary->matching,
            'page'           => $page,
            'per_page'       => self::ORDERS,
            'currency_code'  => $website->shop->currency?->code,
            'source_options' => $sourceOptions,
            'orders'         => $this->orders($website, json_decode($summary->purchase_ids ?? '[]', true)),
        ];
    }

    /**
     * @param array{sources: array<int, string>, visits: string|null, min: float|null, max: float|null, landing: string|null, search: string|null} $filters
     */
    private function summary(Website $website, Carbon $from, Carbon $to, array $filters, int $page): object
    {
        [$conditions, $bindings] = $this->conditions($filters);

        return DB::connection('aiku_no_sticky')->selectOne(
            "WITH tracked AS MATERIALIZED (
                SELECT ce.id AS purchase_id, o.submitted_at, o.net_amount, o.reference, c.name, c.contact_name, c.email,
                    agg.visit_count, agg.source_types, agg.landing_pages
                FROM website_conversion_events ce
                JOIN orders o ON o.id = ce.order_id
                JOIN customers c ON c.id = o.customer_id
                CROSS JOIN LATERAL (
                    SELECT count(*) AS visit_count,
                        array_remove(array_agg(DISTINCT v.traffic_source_type), NULL) AS source_types,
                        array_remove(array_agg(DISTINCT v.landing_page), NULL) AS landing_pages
                    FROM (".$this->orderVisitsSql().") v
                ) agg
                WHERE ce.website_id = ?
                  AND ce.event_type = ?
                  AND ce.website_visitor_id IS NOT NULL
                  AND o.submitted_at BETWEEN ? AND ?
            ),
            matched AS (
                SELECT t.purchase_id, t.submitted_at FROM tracked t WHERE $conditions
            )
            SELECT
                (SELECT count(*) FROM tracked) AS total,
                (SELECT count(*) FROM matched) AS matching,
                (SELECT json_agg(DISTINCT s) FROM tracked t, unnest(t.source_types) s) AS source_types,
                (SELECT json_agg(m.purchase_id ORDER BY m.submitted_at DESC, m.purchase_id DESC)
                    FROM (SELECT * FROM matched ORDER BY submitted_at DESC, purchase_id DESC LIMIT ".self::ORDERS.' OFFSET '.(($page - 1) * self::ORDERS).") m
                ) AS purchase_ids",
            [$website->id, WebsiteConversionEventTypeEnum::PURCHASE->value, $from, $to, ...$bindings]
        );
    }

    /**
     * @param array{sources: array<int, string>, visits: string|null, min: float|null, max: float|null, landing: string|null, search: string|null} $filters
     *
     * @return array{0: string, 1: array<int, mixed>}
     */
    private function conditions(array $filters): array
    {
        $conditions = ['true'];
        $bindings   = [];

        if ($filters['sources']) {
            $conditions[] = 'EXISTS (SELECT 1 FROM unnest(t.source_types) s WHERE s IN ('.implode(',', array_fill(0, count($filters['sources']), '?')).'))';
            $bindings     = [...$bindings, ...$filters['sources']];
        }

        $conditions[] = match ($filters['visits']) {
            '1'     => 't.visit_count = 1',
            '2-3'   => 't.visit_count BETWEEN 2 AND 3',
            '4+'    => 't.visit_count >= 4',
            default => 'true',
        };

        if ($filters['min'] !== null) {
            $conditions[] = 't.net_amount >= ?';
            $bindings[]   = $filters['min'];
        }

        if ($filters['max'] !== null) {
            $conditions[] = 't.net_amount <= ?';
            $bindings[]   = $filters['max'];
        }

        if ($filters['landing'] !== null) {
            $conditions[] = 'EXISTS (SELECT 1 FROM unnest(t.landing_pages) lp WHERE lp COLLATE "C" ILIKE ?)';
            $bindings[]   = $this->containing($filters['landing']);
        }

        if ($filters['search'] !== null) {
            $conditions[] = '(t.reference COLLATE "C" ILIKE ? OR t.name COLLATE "C" ILIKE ? OR t.contact_name COLLATE "C" ILIKE ? OR t.email COLLATE "C" ILIKE ?)';
            $bindings     = [...$bindings, ...array_fill(0, 4, $this->containing($filters['search']))];
        }

        return [implode(' AND ', $conditions), $bindings];
    }

    private function containing(string $text): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $text).'%';
    }

    /**
     * @param array<int, int> $purchaseIds latest first
     *
     * @return array<int, array<string, mixed>>
     */
    private function orders(Website $website, array $purchaseIds): array
    {
        if (!$purchaseIds) {
            return [];
        }

        $organisationSlug = $website->organisation->slug;
        $shopSlug         = $website->shop->slug;

        $purchases = DB::connection('aiku_no_sticky')->table('website_conversion_events as ce')
            ->join('orders as o', 'o.id', '=', 'ce.order_id')
            ->join('customers as c', 'c.id', '=', 'o.customer_id')
            ->leftJoin('currencies as cur', 'cur.id', '=', 'o.currency_id')
            ->whereIn('ce.id', $purchaseIds)
            ->get([
                'ce.id as purchase_id',
                'o.reference',
                'o.slug as order_slug',
                'o.submitted_at',
                'o.net_amount',
                'cur.code as currency_code',
                'c.name',
                'c.contact_name',
                'c.email',
                'c.slug as customer_slug',
            ])
            ->keyBy('purchase_id');

        $visitsByPurchase = $this->visitsOfPurchases($purchaseIds);

        return collect($purchaseIds)
            ->filter(fn (int $purchaseId) => $purchases->has($purchaseId))
            ->map(function (int $purchaseId) use ($purchases, $visitsByPurchase, $organisationSlug, $shopSlug) {
                $purchase = $purchases->get($purchaseId);
                $visits   = $visitsByPurchase->get($purchaseId, collect());

                return [
                    'purchase_id'   => $purchaseId,
                    'reference'     => $purchase->reference,
                    'submitted_at'  => Carbon::parse($purchase->submitted_at)->toIso8601String(),
                    'net_amount'    => (float) $purchase->net_amount,
                    'currency_code' => $purchase->currency_code,
                    'customer'      => [
                        'name'  => $purchase->name ?: ($purchase->contact_name ?: $purchase->email),
                        'route' => [
                            'name'       => 'grp.org.shops.show.crm.customers.show',
                            'parameters' => [$organisationSlug, $shopSlug, $purchase->customer_slug],
                        ],
                    ],
                    'order_route'   => [
                        'name'       => 'grp.org.shops.show.ordering.orders.show',
                        'parameters' => [$organisationSlug, $shopSlug, $purchase->order_slug],
                    ],
                    'visits'        => $visits->count(),
                    'sources'       => $visits->map(fn (object $visit) => $this->sourceOf($visit))->values()->all(),
                ];
            })
            ->values()
            ->all();
    }
}
