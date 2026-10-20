<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Fri, 16 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\WebsitePageView;

use App\Enums\Web\WebsiteConversionEvent\WebsiteConversionEventTypeEnum;
use App\Models\Web\Website;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * One website order broken down visit by visit: where each visit came from, which page it landed
 * on and the pages it opened before the order.
 */
class GetWebsiteOrderJourney
{
    use AsAction;
    use WithOrderVisits;

    private const PAGES_PER_VISIT = 25;
    private const PAGE_VIEW_LIMIT = 300;
    private const MAX_LABEL       = 60;

    /**
     * @return array<string, mixed>|null
     */
    public function handle(Website $website, int $purchaseId): ?array
    {
        $purchase = DB::connection('aiku_no_sticky')->table('website_conversion_events as ce')
            ->join('orders as o', 'o.id', '=', 'ce.order_id')
            ->join('customers as c', 'c.id', '=', 'o.customer_id')
            ->leftJoin('currencies as cur', 'cur.id', '=', 'o.currency_id')
            ->where('ce.id', $purchaseId)
            ->where('ce.website_id', $website->id)
            ->where('ce.event_type', WebsiteConversionEventTypeEnum::PURCHASE->value)
            ->first([
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
            ]);

        if (!$purchase) {
            return null;
        }

        $visits    = $this->visitsOfPurchases([$purchaseId])->get($purchaseId, collect());
        $pageViews = $this->pageViews($visits->pluck('visit_id')->all(), $purchase->submitted_at)->groupBy('visit_id');

        $visitRows = $visits->map(fn (object $visit) => [
            'id'           => (int) $visit->visit_id,
            'started_at'   => Carbon::parse($visit->first_seen_at)->toIso8601String(),
            'ended_at'     => Carbon::parse($visit->last_seen_at)->toIso8601String(),
            'source'       => $this->sourceOf($visit),
            'landing_path' => $visit->landing_page ? (parse_url($visit->landing_page, PHP_URL_PATH) ?: '/') : null,
            'pages'        => $this->pagesOf($pageViews->get($visit->visit_id, collect())),
        ])->values()->all();

        $organisationSlug = $website->organisation->slug;
        $shopSlug         = $website->shop->slug;

        return [
            'purchase_id'   => (int) $purchase->purchase_id,
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
            'visits'        => $visitRows,
        ];
    }

    /**
     * @param array<int, int> $visitIds
     *
     * @return Collection<int, object>
     */
    private function pageViews(array $visitIds, string $submittedAt): Collection
    {
        if (!$visitIds) {
            return collect();
        }

        return collect(DB::connection('aiku_no_sticky')->table('website_page_views')
            ->whereIn('website_visitor_id', $visitIds)
            ->where('created_at', '<=', $submittedAt)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::PAGE_VIEW_LIMIT)
            ->get(['website_visitor_id as visit_id', 'page_path', 'page_type', 'page_sub_type', 'webpage_id', 'created_at'])
            ->all())
            ->reverse()
            ->values();
    }

    /**
     * @param Collection<int, object> $pageViews earliest first
     *
     * @return array<int, array{path: string, type: string|null, viewed_at: string}>
     */
    private function pagesOf(Collection $pageViews): array
    {
        $pages = [];

        foreach ($pageViews as $pageView) {
            $path = self::pathLabel($pageView->page_path);

            if ($pages && $pages[array_key_last($pages)]['path'] === $path) {
                continue;
            }

            $hasSubType = filled($pageView->page_sub_type) && $pageView->page_sub_type !== $pageView->page_type;

            $pages[] = [
                'path'      => $path,
                'type'      => $pageView->webpage_id !== null && filled($pageView->page_type)
                    ? ($hasSubType ? $pageView->page_type.' / '.$pageView->page_sub_type : $pageView->page_type)
                    : null,
                'viewed_at' => Carbon::parse($pageView->created_at)->toIso8601String(),
            ];
        }

        return array_slice($pages, -self::PAGES_PER_VISIT);
    }

    public static function pathLabel(string $path): string
    {
        $normalised = preg_replace('#^(/app/[a-z0-9_-]+)/[^/]*\d[^/]*$#i', '$1/:id', $path) ?? $path;

        return mb_strimwidth($normalised, 0, self::MAX_LABEL, '…');
    }
}
