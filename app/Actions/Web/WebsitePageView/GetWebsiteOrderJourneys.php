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
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * The latest orders placed on the website, each with its customer and the traffic source of every
 * visit that led to it, so the sources read as a path: Email, then Direct, then Direct.
 */
class GetWebsiteOrderJourneys
{
    use AsAction;
    use WithOrderVisits;

    public const ORDERS      = 50;
    public const DAY_OPTIONS = [30, 90, 180];

    /**
     * @return array<int, array{purchase_id: int, reference: string, submitted_at: string, net_amount: float, currency_code: string|null, customer: array{name: string|null, route: array{name: string, parameters: array<int, string>}}, order_route: array{name: string, parameters: array<int, string>}, visits: int, sources: array<int, array{type: string|null, label: string, reference: string|null}>}>
     */
    public function handle(Website $website, Carbon $from, Carbon $to): array
    {
        $organisationSlug = $website->organisation->slug;
        $shopSlug         = $website->shop->slug;

        $purchases = DB::connection('aiku_no_sticky')->table('website_conversion_events as ce')
            ->join('orders as o', 'o.id', '=', 'ce.order_id')
            ->join('customers as c', 'c.id', '=', 'o.customer_id')
            ->leftJoin('currencies as cur', 'cur.id', '=', 'o.currency_id')
            ->where('ce.website_id', $website->id)
            ->where('ce.event_type', WebsiteConversionEventTypeEnum::PURCHASE->value)
            ->whereNotNull('ce.website_visitor_id')
            ->whereBetween('o.submitted_at', [$from, $to])
            ->orderByDesc('o.submitted_at')
            ->limit(self::ORDERS)
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
            ]);

        if ($purchases->isEmpty()) {
            return [];
        }

        $visitsByPurchase = $this->visitsOfPurchases($purchases->pluck('purchase_id')->all());

        return $purchases->map(function (object $purchase) use ($visitsByPurchase, $organisationSlug, $shopSlug) {
            $visits = $visitsByPurchase->get($purchase->purchase_id, collect());

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
                'visits'        => $visits->count(),
                'sources'       => $visits->map(fn (object $visit) => $this->sourceOf($visit))->values()->all(),
            ];
        })->values()->all();
    }
}
