<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Mon, 12 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Discounts\OfferCampaign\UI;

use App\Actions\Discounts\UI\GetOffersInsightsData;
use App\Actions\OrgAction;
use App\Enums\Discounts\Offer\OfferStateEnum;
use App\Http\Resources\Catalogue\OfferCampaignsInsightsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\Discounts\OfferCampaign;
use App\Services\QueryBuilder;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class IndexOfferCampaignsInsights extends OrgAction
{
    public function handle(Shop $shop, ?OfferCampaign $offerCampaign = null, ?string $offerType = null, ?string $fromDate = null, ?string $toDate = null, $prefix = null): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $redemptionOrders = GetOffersInsightsData::make()->buildRedemptionOrdersQuery($shop, $offerCampaign, $offerType, $fromDate, $toDate);

        $campaignOrders = DB::query()->fromSub($redemptionOrders, 'redemption_orders')
            ->join('offers', 'offers.id', '=', 'redemption_orders.offer_id')
            ->selectRaw('
                offers.offer_campaign_id,
                redemption_orders.offer_id,
                redemption_orders.order_id,
                redemption_orders.customer_id,
                redemption_orders.order_net_amount,
                redemption_orders.discount_amount,
                redemption_orders.used_at,
                ROW_NUMBER() OVER (PARTITION BY offers.offer_campaign_id, redemption_orders.order_id ORDER BY redemption_orders.offer_id) as order_rank
            ');

        $campaignRedemptions = DB::query()->fromSub($campaignOrders, 'campaign_orders')
            ->groupBy('offer_campaign_id')
            ->selectRaw('
                offer_campaign_id,
                COUNT(DISTINCT order_id) as redemptions,
                COUNT(DISTINCT customer_id) as redemption_customers,
                COUNT(DISTINCT offer_id) as redeemed_offers,
                SUM(discount_amount) as discounted_amount,
                SUM(order_net_amount) FILTER (WHERE order_rank = 1) as revenue_net_amount,
                MAX(used_at) as last_used_at
            ');

        $query = QueryBuilder::for(OfferCampaign::class);
        $query->where('offer_campaigns.shop_id', $shop->id);
        $query->where('offer_campaigns.status', true);

        if ($offerCampaign) {
            $query->where('offer_campaigns.id', $offerCampaign->id);
        }

        $query->leftJoinSub($this->buildActiveOffersQuery($shop, $offerType, $fromDate, $toDate, $redemptionOrders), 'campaign_offers', 'campaign_offers.offer_campaign_id', '=', 'offer_campaigns.id');
        $query->leftJoinSub($campaignRedemptions, 'campaign_redemptions', 'campaign_redemptions.offer_campaign_id', '=', 'offer_campaigns.id');

        return $query->defaultSort('-redemptions')
            ->select([
                'offer_campaigns.id',
                'offer_campaigns.slug',
                'offer_campaigns.code',
                'offer_campaigns.name',
                'offer_campaigns.type',
                DB::raw('COALESCE(campaign_offers.active_offers, 0) as active_offers'),
                DB::raw('COALESCE(campaign_redemptions.redeemed_offers, 0) as redeemed_offers'),
                DB::raw('COALESCE(campaign_redemptions.redemptions, 0) as redemptions'),
                DB::raw('COALESCE(campaign_redemptions.redemption_customers, 0) as redemption_customers'),
                DB::raw('COALESCE(campaign_redemptions.discounted_amount, 0) as discounted_amount'),
                DB::raw('COALESCE(campaign_redemptions.revenue_net_amount, 0) as revenue_net_amount'),
                'campaign_redemptions.last_used_at',
            ])
            ->allowedSorts(['name', 'active_offers', 'redeemed_offers', 'redemptions', 'redemption_customers', 'discounted_amount', 'revenue_net_amount', 'last_used_at'])
            ->withPaginator($prefix, tableName: request()->route()?->getName())
            ->withQueryString();
    }

    protected function buildActiveOffersQuery(Shop $shop, ?string $offerType, ?string $fromDate, ?string $toDate, Builder $redemptionOrders): Builder
    {
        $periodStart = $fromDate ? Carbon::createFromFormat('Ymd', $fromDate)->startOfDay() : null;
        $periodEnd   = $toDate ? Carbon::createFromFormat('Ymd', $toDate)->endOfDay() : null;

        $wasRunningInPeriod = function (Builder $query) use ($periodStart, $periodEnd) {
            $query->where('offers.state', '!=', OfferStateEnum::IN_PROCESS->value)
                ->when($periodEnd, fn (Builder $query) => $query->whereRaw('COALESCE(offers.start_at, offers.created_at) <= ?', [$periodEnd]))
                ->when($periodStart, fn (Builder $query) => $query->where(function (Builder $query) use ($periodStart) {
                    $query->where(function (Builder $query) use ($periodStart) {
                        $query->where('offers.state', OfferStateEnum::ACTIVE->value)
                            ->where(fn (Builder $query) => $query->whereNull('offers.end_at')->orWhere('offers.end_at', '>=', $periodStart));
                    })->orWhere(function (Builder $query) use ($periodStart) {
                        $query->where('offers.state', OfferStateEnum::SUSPENDED->value)
                            ->whereRaw('COALESCE(offers.last_suspended_at, offers.end_at) >= ?', [$periodStart]);
                    })->orWhere(function (Builder $query) use ($periodStart) {
                        $query->where('offers.state', OfferStateEnum::FINISHED->value)
                            ->where('offers.end_at', '>=', $periodStart);
                    });
                }));
        };

        $wasRedeemedInPeriod = fn (Builder $query) => $query->whereIn(
            'offers.id',
            DB::query()->fromSub($redemptionOrders, 'redemption_orders')->select('redemption_orders.offer_id')
        );

        return DB::table('offers')
            ->where('offers.shop_id', $shop->id)
            ->whereNull('offers.deleted_at')
            ->when($offerType, fn (Builder $query) => $query->where('offers.type', $offerType))
            ->where(function (Builder $query) use ($wasRunningInPeriod, $wasRedeemedInPeriod, $periodStart, $periodEnd) {
                $query->where($wasRunningInPeriod)
                    ->when($periodStart || $periodEnd, fn (Builder $query) => $query->orWhere($wasRedeemedInPeriod));
            })
            ->groupBy('offers.offer_campaign_id')
            ->selectRaw('offers.offer_campaign_id, COUNT(*) as active_offers');
    }

    public function tableStructure($prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table->withEmptyState([
                'icons'       => ['fal fa-comment-dollar'],
                'title'       => __('No campaigns to show'),
                'description' => __('This shop has no campaign matching the selected filters'),
            ]);

            $table->column(key: 'type', label: '', type: 'icon', sortable: false);
            $table->column(key: 'name', label: __('Campaign'), sortable: true);
            $table->column(
                key: 'active_offers',
                label: __('Active in period'),
                tooltip: __('Offers that were running at some point in the selected period, plus any offer used on an order in it. With All selected, every offer that has started.'),
                sortable: true,
                align: 'right'
            );
            $table->column(key: 'redeemed_offers', label: __('Offers redeemed'), tooltip: __('Offers used on at least one order in the selected period'), sortable: true, align: 'right');
            $table->column(key: 'redemptions', label: __('Redemptions'), sortable: true, align: 'right');
            $table->column(key: 'redemption_customers', label: __('Customers'), sortable: true, align: 'right');
            $table->column(key: 'discounted_amount', label: __('Discount given'), sortable: true, align: 'right');
            $table->column(key: 'revenue_net_amount', label: __('Revenue influenced'), sortable: true, align: 'right');
            $table->column(key: 'last_used_at', label: __('Last used'), sortable: true, align: 'right');

            $table->defaultSort('-redemptions');
        };
    }

    public function jsonResponse(LengthAwarePaginator $offerCampaigns): AnonymousResourceCollection
    {
        return OfferCampaignsInsightsResource::collection($offerCampaigns);
    }
}
