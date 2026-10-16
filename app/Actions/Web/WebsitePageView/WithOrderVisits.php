<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Fri, 16 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\WebsitePageView;

use App\Enums\Web\WebsiteVisitor\WebsiteVisitorChannelEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The visits that led to a website order. A visit is a `website_visitors` row, which carries the
 * traffic source it arrived from, and the visits of an order are the ones of its customer's
 * logged-in accounts in the VISIT_LOOKBACK_DAYS before it, plus the visit the order was placed in.
 */
trait WithOrderVisits
{
    public const VISIT_LOOKBACK_DAYS = 30;
    public const MAX_VISITS          = 10;

    /**
     * @param array<int, int> $purchaseIds
     *
     * @return Collection<int, Collection<int, object>> the visits of each purchase, earliest first
     */
    protected function visitsOfPurchases(array $purchaseIds): Collection
    {
        $placeholders = implode(',', array_fill(0, count($purchaseIds), '?'));

        $visits = DB::connection('aiku_no_sticky')->select(
            "SELECT ce.id AS purchase_id, v.id AS visit_id, v.first_seen_at, v.last_seen_at, v.traffic_source_type, v.traffic_source_reference, v.landing_page
            FROM website_conversion_events ce
            JOIN orders o ON o.id = ce.order_id
            JOIN LATERAL (
                SELECT w.id, w.first_seen_at, w.last_seen_at, w.traffic_source_type, w.traffic_source_reference, w.landing_page
                FROM website_visitors w
                WHERE w.website_id = ce.website_id
                  AND w.first_seen_at <= o.submitted_at
                  AND w.last_seen_at >= o.submitted_at - interval '".self::VISIT_LOOKBACK_DAYS." days'
                  AND (
                      w.id = ce.website_visitor_id
                      OR w.web_user_id IN (SELECT wu.id FROM web_users wu WHERE wu.customer_id = o.customer_id)
                  )
                ORDER BY w.first_seen_at DESC
                LIMIT ".self::MAX_VISITS."
            ) v ON true
            WHERE ce.id IN ($placeholders)
            ORDER BY ce.id, v.first_seen_at",
            $purchaseIds
        );

        return collect($visits)->groupBy('purchase_id');
    }

    /**
     * @return array{type: string|null, label: string, reference: string|null}
     */
    protected function sourceOf(object $visit): array
    {
        return [
            'type'      => $visit->traffic_source_type,
            'label'     => WebsiteVisitorChannelEnum::typeLabel($visit->traffic_source_type) ?? __('Unknown'),
            'reference' => $visit->traffic_source_reference,
        ];
    }
}
