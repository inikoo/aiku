<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\SocialMedia;

use App\Actions\CRM\TrafficSource\WithAggregatedChannelQueries;
use App\Enums\CRM\SocialMedia\SocialMediaPlatformEnum;
use App\Enums\CRM\SocialMedia\SocialMediaPostTypeEnum;
use App\Enums\CRM\TrafficSource\TrafficSourcesTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Organic figures are the posts published in the period, as last exported; followers come from the
 * accounts' daily exports. Paid figures are the Meta ad spend fetched every night and the revenue
 * attribution credits to it, in the parent's currency.
 */
class GetSocialMediaPerformance
{
    use AsAction;
    use WithAggregatedChannelQueries;

    public const string ENGAGEMENTS = '(p.reactions + p.comments + p.shares + p.saves)';

    private const int TOP_POSTS = 50;

    /**
     * @param array{shop?: string|null, country?: string|null, platform?: string|null} $filters
     */
    public function handle(Organisation|Group $parent, ?Carbon $from, ?Carbon $to, array $filters = []): array
    {
        $allShops = $parent->shops()->with('country')->orderBy('name')->get();
        $shops    = $this->filteredShops($allShops, $filters);
        $platform = SocialMediaPlatformEnum::tryFrom((string) ($filters['platform'] ?? ''));

        [$previousFrom, $previousTo] = $this->previousPeriod($from, $to);

        $isOrganisation = $parent instanceof Organisation;
        $costColumn     = $isOrganisation ? 'org_amount' : 'grp_amount';
        $revenueColumn  = $isOrganisation ? 'org_net_amount' : 'grp_net_amount';
        $adTypes        = $this->adTypes($platform);

        $organic         = $this->organicTotals($shops, $platform, $from, $to);
        $previousOrganic = $previousFrom ? $this->organicTotals($shops, $platform, $previousFrom, $previousTo) : null;
        $paidChannels    = $this->paidChannels($shops, $adTypes, $from, $to, $costColumn, $revenueColumn);
        $paid            = $this->paidTotals($paidChannels);
        $previousPaid    = $previousFrom ? $this->paidTotals($this->paidChannels($shops, $adTypes, $previousFrom, $previousTo, $costColumn, $revenueColumn)) : null;

        return [
            'from'          => $from?->toDateString(),
            'to'            => $to?->toDateString(),
            'previous_from' => $previousFrom?->toDateString(),
            'previous_to'   => $previousTo?->toDateString(),
            'currency_code' => $parent->currency->code,
            'filters'       => [
                'shop'     => $filters['shop'] ?? null,
                'country'  => $filters['country'] ?? null,
                'platform' => $platform?->value,
            ],
            'options'       => $this->options($allShops),
            'data_until'    => $this->dataUntil($allShops),
            'kpis'          => [
                'current'  => array_merge($organic, $paid),
                'previous' => $previousOrganic ? array_merge($previousOrganic, $previousPaid) : null,
            ],
            'trend'         => $this->trend($shops, $platform, $from, $to),
            'platforms'     => $this->platforms($shops, $platform, $from, $to),
            'formats'       => $this->formats($shops, $platform, $from, $to),
            'top_posts'     => $this->topPosts($shops, $platform, $from, $to),
            'accounts'      => $this->accounts($shops, $platform, $from, $to),
            'paid'          => [
                'channels'  => $paidChannels,
                'campaigns' => $this->paidCampaigns($parent, $shops, $adTypes, $from, $to, $costColumn, $revenueColumn),
            ],
        ];
    }

    /**
     * Every post in the period under the filters, for the export.
     *
     * @param array{shop?: string|null, country?: string|null, platform?: string|null} $filters
     */
    public function exportQuery(Organisation|Group $parent, ?Carbon $from, ?Carbon $to, array $filters = []): Builder
    {
        return $this->posts(
            $this->filteredShops($parent->shops()->with('country')->get(), $filters),
            SocialMediaPlatformEnum::tryFrom((string) ($filters['platform'] ?? '')),
            $from,
            $to
        )
            ->join('shops as s', 's.id', '=', 'a.shop_id')
            ->leftJoin('countries as co', 'co.id', '=', 's.country_id')
            ->selectRaw('p.published_at, a.platform, a.name as account_name, s.name as shop_name, co.code as country_code, p.type, p.description, p.permalink, p.reach, p.impressions, p.reactions, p.comments, p.shares, p.saves, p.link_clicks, p.follows, '.self::ENGAGEMENTS.' as engagements')
            ->orderBy('p.published_at');
    }

    /**
     * @param Collection<int, Shop>                                                    $shops
     * @param array{shop?: string|null, country?: string|null, platform?: string|null} $filters
     *
     * @return Collection<int, Shop>
     */
    private function filteredShops(Collection $shops, array $filters): Collection
    {
        return $shops
            ->when($filters['shop'] ?? null, fn (Collection $shops, string $slug) => $shops->where('slug', $slug))
            ->when($filters['country'] ?? null, fn (Collection $shops, string $code) => $shops->filter(fn (Shop $shop) => $shop->country?->code === $code))
            ->values();
    }

    /**
     * @return array{0: Carbon|null, 1: Carbon|null}
     */
    private function previousPeriod(?Carbon $from, ?Carbon $to): array
    {
        if (!$from) {
            return [null, null];
        }

        $to ??= now()->endOfDay();
        $days = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;

        return [
            $from->copy()->subDays($days)->startOfDay(),
            $from->copy()->subDay()->endOfDay(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function adTypes(?SocialMediaPlatformEnum $platform): array
    {
        return match ($platform) {
            SocialMediaPlatformEnum::FACEBOOK  => [TrafficSourcesTypeEnum::META_ADS->value],
            SocialMediaPlatformEnum::INSTAGRAM => [TrafficSourcesTypeEnum::INSTAGRAM_ADS->value],
            SocialMediaPlatformEnum::TIKTOK    => [TrafficSourcesTypeEnum::TIKTOK_ADS->value],
            null                               => [TrafficSourcesTypeEnum::META_ADS->value, TrafficSourcesTypeEnum::INSTAGRAM_ADS->value, TrafficSourcesTypeEnum::TIKTOK_ADS->value],
        };
    }

    /**
     * @param Collection<int, Shop> $shops
     */
    private function posts(Collection $shops, ?SocialMediaPlatformEnum $platform, ?Carbon $from, ?Carbon $to): Builder
    {
        return DB::table('social_media_posts as p')
            ->join('social_media_accounts as a', 'a.id', '=', 'p.social_media_account_id')
            ->whereIn('a.shop_id', $shops->pluck('id'))
            ->when($platform, fn (Builder $query) => $query->where('a.platform', $platform->value))
            ->when($from, fn (Builder $query) => $query->where('p.published_at', '>=', $from))
            ->when($to, fn (Builder $query) => $query->where('p.published_at', '<=', $to));
    }

    /**
     * @param Collection<int, Shop> $shops
     */
    private function accountsQuery(Collection $shops, ?SocialMediaPlatformEnum $platform): Builder
    {
        return DB::table('social_media_accounts as a')
            ->whereIn('a.shop_id', $shops->pluck('id'))
            ->when($platform, fn (Builder $query) => $query->where('a.platform', $platform->value));
    }

    private function rate(float|int $part, float|int $whole): ?float
    {
        return $whole > 0 ? round($part / $whole * 100, 2) : null;
    }

    /**
     * @param Collection<int, Shop> $shops
     *
     * @return array{posts: int, reach: int, impressions: int, engagements: int, engagement_rate: float|null, link_clicks: int, followers: int|null, follower_growth: int|null}
     */
    private function organicTotals(Collection $shops, ?SocialMediaPlatformEnum $platform, ?Carbon $from, ?Carbon $to): array
    {
        $totals = $this->posts($shops, $platform, $from, $to)
            ->selectRaw('COUNT(*) as posts, COALESCE(SUM(p.reach), 0) as reach, COALESCE(SUM(p.impressions), 0) as impressions, COALESCE(SUM('.self::ENGAGEMENTS.'), 0) as engagements, COALESCE(SUM(p.link_clicks), 0) as link_clicks, COALESCE(SUM(p.follows), 0) as follows')
            ->first();

        $accountIds = $this->accountsQuery($shops, $platform)->pluck('a.id');
        $followers  = $this->followersAt($accountIds, $to);
        $before     = $from ? $this->followersAt($accountIds, $from->copy()->subDay()) : collect();

        $newFollows = DB::table('social_media_account_daily_stats')
            ->whereIn('social_media_account_id', $accountIds)
            ->when($from, fn (Builder $query) => $query->where('date', '>=', $from->toDateString()))
            ->when($to, fn (Builder $query) => $query->where('date', '<=', $to->toDateString()))
            ->sum('new_follows');

        $growthFromFollowers = $before->isNotEmpty()
            ? $followers->only($before->keys()->all())->sum() - $before->sum()
            : null;

        return [
            'posts'           => (int) $totals->posts,
            'reach'           => (int) $totals->reach,
            'impressions'     => (int) $totals->impressions,
            'engagements'     => (int) $totals->engagements,
            'engagement_rate' => $this->rate((int) $totals->engagements, (int) $totals->reach),
            'link_clicks'     => (int) $totals->link_clicks,
            'followers'       => $followers->isNotEmpty() ? (int) $followers->sum() : null,
            'follower_growth' => $growthFromFollowers ?? ((int) $newFollows ?: ((int) $totals->follows ?: null)),
        ];
    }

    /**
     * The last follower count each account reported on or before the date; the account's current
     * count when no date is given.
     *
     * @return Collection<int, int>
     */
    private function followersAt(Collection $accountIds, ?Carbon $date): Collection
    {
        $fromStats = DB::table('social_media_account_daily_stats')
            ->whereIn('social_media_account_id', $accountIds)
            ->whereNotNull('followers')
            ->when($date, fn (Builder $query) => $query->where('date', '<=', $date->toDateString()))
            ->selectRaw('DISTINCT ON (social_media_account_id) social_media_account_id, followers')
            ->orderBy('social_media_account_id')
            ->orderByDesc('date')
            ->pluck('followers', 'social_media_account_id')
            ->map(fn ($followers) => (int) $followers);

        if ($date !== null && $date->lt(now()->startOfDay())) {
            return $fromStats;
        }

        $current = DB::table('social_media_accounts')
            ->whereIn('id', $accountIds)
            ->whereNotNull('followers')
            ->pluck('followers', 'id')
            ->map(fn ($followers) => (int) $followers);

        return $fromStats->union($current);
    }

    /**
     * @param array<int, array<string, mixed>> $channels
     *
     * @return array{spend: float, revenue: float, orders: float, roas: float|null, cpa: float|null, ad_impressions: int, ad_clicks: int, ctr: float|null}
     */
    private function paidTotals(array $channels): array
    {
        $spend       = round(array_sum(array_column($channels, 'spend')), 2);
        $revenue     = round(array_sum(array_column($channels, 'revenue')), 2);
        $orders      = round(array_sum(array_column($channels, 'orders')), 2);
        $impressions = (int) array_sum(array_column($channels, 'impressions'));
        $clicks      = (int) array_sum(array_column($channels, 'clicks'));

        return [
            'spend'          => $spend,
            'revenue'        => $revenue,
            'orders'         => $orders,
            'roas'           => $spend > 0 ? round($revenue / $spend, 2) : null,
            'cpa'            => $orders > 0 ? round($spend / $orders, 2) : null,
            'ad_impressions' => $impressions,
            'ad_clicks'      => $clicks,
            'ctr'            => $this->rate($clicks, $impressions),
        ];
    }

    /**
     * @param array<int, string> $adTypes
     *
     * @return array<int, array{type: string, label: string, spend: float, revenue: float, orders: float, roas: float|null, impressions: int, clicks: int, ctr: float|null}>
     */
    private function paidChannels(Collection $shops, array $adTypes, ?Carbon $from, ?Carbon $to, string $costColumn, string $revenueColumn): array
    {
        $attributionFrom = $this->clipToAttributionStart($from);
        $metrics         = $this->adMetrics($shops, $adTypes, $from, $to, 'ts.type');
        $spendByType     = $this->spendByType($shops, $from, $to, $costColumn);
        $revenueByType   = $this->revenueByType($shops, $attributionFrom, $to, $revenueColumn);
        $ordersByType    = $this->ordersByType($shops, $attributionFrom, $to);

        return collect($adTypes)
            ->map(function (string $type) use ($spendByType, $revenueByType, $ordersByType, $metrics) {
                $spend   = (float) ($spendByType[$type] ?? 0);
                $revenue = (float) ($revenueByType[$type] ?? 0);
                $orders  = (float) ($ordersByType[$type] ?? 0);
                $clicks  = (int) ($metrics[$type]->clicks ?? 0);
                $shown   = (int) ($metrics[$type]->impressions ?? 0);

                return [
                    'type'        => $type,
                    'label'       => TrafficSourcesTypeEnum::labels()[$type] ?? $type,
                    'spend'       => round($spend, 2),
                    'revenue'     => round($revenue, 2),
                    'orders'      => round($orders, 2),
                    'roas'        => $spend > 0 ? round($revenue / $spend, 2) : null,
                    'impressions' => $shown,
                    'clicks'      => $clicks,
                    'ctr'         => $this->rate($clicks, $shown),
                ];
            })
            ->filter(fn (array $channel) => $channel['spend'] > 0 || $channel['revenue'] > 0 || $channel['impressions'] > 0)
            ->values()
            ->all();
    }

    /**
     * @param array<int, string> $adTypes
     *
     * @return Collection<int|string, object{impressions: int, clicks: int}>
     */
    private function adMetrics(Collection $shops, array $adTypes, ?Carbon $from, ?Carbon $to, string $groupBy): Collection
    {
        return DB::table('traffic_source_campaign_metrics as m')
            ->join('traffic_source_campaigns as tc', 'tc.id', '=', 'm.traffic_source_campaign_id')
            ->join('traffic_sources as ts', 'ts.id', '=', 'tc.traffic_source_id')
            ->whereIn('ts.shop_id', $shops->pluck('id'))
            ->whereIn('ts.type', $adTypes)
            ->when($from, fn (Builder $query) => $query->where('m.date', '>=', $from->toDateString()))
            ->when($to, fn (Builder $query) => $query->where('m.date', '<=', $to->toDateString()))
            ->groupBy($groupBy)
            ->selectRaw("$groupBy as bucket, SUM(m.impressions) as impressions, SUM(m.clicks) as clicks")
            ->get()
            ->keyBy('bucket');
    }

    /**
     * @param array<int, string> $adTypes
     *
     * @return array<int, array<string, mixed>>
     */
    private function paidCampaigns(Organisation|Group $parent, Collection $shops, array $adTypes, ?Carbon $from, ?Carbon $to, string $costColumn, string $revenueColumn): array
    {
        $attributionFrom = $this->clipToAttributionStart($from);
        $spend           = $this->spendByType($shops, $from, $to, $costColumn, 'c.traffic_source_campaign_id');
        $revenue         = $this->revenueByType($shops, $attributionFrom, $to, $revenueColumn, 'p.traffic_source_campaign_id');
        $orders          = $this->ordersByType($shops, $attributionFrom, $to, 'p.traffic_source_campaign_id');
        $metrics = $this->adMetrics($shops, $adTypes, $from, $to, 'tc.id');
        $isGroup = $parent instanceof Group;

        return DB::table('traffic_source_campaigns as tc')
            ->join('traffic_sources as ts', 'ts.id', '=', 'tc.traffic_source_id')
            ->join('shops as s', 's.id', '=', 'ts.shop_id')
            ->join('organisations as o', 'o.id', '=', 's.organisation_id')
            ->whereIn('ts.shop_id', $shops->pluck('id'))
            ->whereIn('ts.type', $adTypes)
            ->select('tc.id', 'tc.name', 'ts.type', 's.name as shop_name', 'o.name as organisation_name')
            ->get()
            ->map(function ($campaign) use ($spend, $revenue, $orders, $metrics, $isGroup) {
                $spent     = round((float) ($spend[$campaign->id] ?? 0), 2);
                $earned    = round((float) ($revenue[$campaign->id] ?? 0), 2);
                $purchases = round((float) ($orders[$campaign->id] ?? 0), 2);
                $clicks    = (int) ($metrics[$campaign->id]->clicks ?? 0);
                $shown     = (int) ($metrics[$campaign->id]->impressions ?? 0);

                return [
                    'name'          => $campaign->name,
                    'channel'       => TrafficSourcesTypeEnum::labels()[$campaign->type] ?? $campaign->type,
                    'owner'         => $isGroup ? $campaign->organisation_name.' · '.$campaign->shop_name : $campaign->shop_name,
                    'spend'         => $spent,
                    'revenue'       => $earned,
                    'orders'        => $purchases,
                    'impressions'   => $shown,
                    'clicks'        => $clicks,
                    'ctr'           => $this->rate($clicks, $shown),
                    'cpa'           => $purchases > 0 ? round($spent / $purchases, 2) : null,
                    'roas'          => $spent > 0 ? round($earned / $spent, 2) : null,
                ];
            })
            ->filter(fn (array $campaign) => $campaign['spend'] > 0 || $campaign['revenue'] > 0)
            ->sortByDesc('spend')
            ->values()
            ->all();
    }

    /**
     * @param Collection<int, Shop> $shops
     *
     * @return array{bucket: string, points: array<int, array{date: string, posts: int, reach: int, engagements: int, followers: int|null}>}
     */
    private function trend(Collection $shops, ?SocialMediaPlatformEnum $platform, ?Carbon $from, ?Carbon $to): array
    {
        $days   = $from ? $from->diffInDays($to ?? now()) : PHP_INT_MAX;
        $bucket = match (true) {
            $days <= 62  => 'day',
            $days <= 400 => 'week',
            default      => 'month',
        };

        $posts = $this->posts($shops, $platform, $from, $to)
            ->selectRaw("date_trunc('$bucket', p.published_at)::date as bucket, COUNT(*) as posts, SUM(p.reach) as reach, SUM(".self::ENGAGEMENTS.') as engagements')
            ->groupBy('bucket')
            ->get()
            ->keyBy(fn ($row) => (string) $row->bucket);

        $followers = DB::query()
            ->fromSub(
                DB::table('social_media_account_daily_stats')
                    ->whereIn('social_media_account_id', $this->accountsQuery($shops, $platform)->pluck('a.id'))
                    ->whereNotNull('followers')
                    ->when($from, fn (Builder $query) => $query->where('date', '>=', $from->toDateString()))
                    ->when($to, fn (Builder $query) => $query->where('date', '<=', $to->toDateString()))
                    ->selectRaw("DISTINCT ON (social_media_account_id, date_trunc('$bucket', date)) social_media_account_id, date_trunc('$bucket', date)::date as bucket, followers")
                    ->orderBy('social_media_account_id')
                    ->orderByRaw("date_trunc('$bucket', date)")
                    ->orderByDesc('date'),
                'last_in_bucket'
            )
            ->groupBy('bucket')
            ->selectRaw('bucket, SUM(followers) as followers')
            ->pluck('followers', 'bucket')
            ->mapWithKeys(fn ($value, $key) => [(string) $key => (int) $value]);

        $dates = $posts->keys()->merge($followers->keys())->unique()->sort()->values();

        if ($from && $bucket === 'day') {
            $dates = collect(range(0, (int) $from->copy()->startOfDay()->diffInDays(($to ?? now())->copy()->startOfDay())))
                ->map(fn (int $offset) => $from->copy()->addDays($offset)->toDateString());
        }

        return [
            'bucket' => $bucket,
            'points' => $dates->map(fn (string $date) => [
                'date'        => $date,
                'posts'       => (int) ($posts[$date]->posts ?? 0),
                'reach'       => (int) ($posts[$date]->reach ?? 0),
                'engagements' => (int) ($posts[$date]->engagements ?? 0),
                'followers'   => $followers[$date] ?? null,
            ])->values()->all(),
        ];
    }

    /**
     * @param Collection<int, Shop> $shops
     *
     * @return array<int, array<string, mixed>>
     */
    private function platforms(Collection $shops, ?SocialMediaPlatformEnum $platform, ?Carbon $from, ?Carbon $to): array
    {
        $rows = $this->posts($shops, $platform, $from, $to)
            ->groupBy('a.platform')
            ->selectRaw('a.platform, COUNT(*) as posts, SUM(p.reach) as reach, SUM('.self::ENGAGEMENTS.') as engagements')
            ->get();

        $followers = $this->accountsQuery($shops, $platform)
            ->groupBy('a.platform')
            ->selectRaw('a.platform, SUM(a.followers) as followers')
            ->pluck('followers', 'platform');

        $totalReach = (int) $rows->sum('reach');

        return $rows->map(fn ($row) => [
            'platform'        => $row->platform,
            'label'           => SocialMediaPlatformEnum::labels()[$row->platform] ?? $row->platform,
            'posts'           => (int) $row->posts,
            'reach'           => (int) $row->reach,
            'share'           => $this->rate((int) $row->reach, $totalReach),
            'engagements'     => (int) $row->engagements,
            'engagement_rate' => $this->rate((int) $row->engagements, (int) $row->reach),
            'followers'       => isset($followers[$row->platform]) ? (int) $followers[$row->platform] : null,
        ])->sortByDesc('reach')->values()->all();
    }

    /**
     * @param Collection<int, Shop> $shops
     *
     * @return array<int, array<string, mixed>>
     */
    private function formats(Collection $shops, ?SocialMediaPlatformEnum $platform, ?Carbon $from, ?Carbon $to): array
    {
        return $this->posts($shops, $platform, $from, $to)
            ->groupBy('p.type')
            ->selectRaw('p.type, COUNT(*) as posts, SUM(p.reach) as reach, SUM('.self::ENGAGEMENTS.') as engagements, SUM(p.saves) as saves, SUM(p.shares) as shares')
            ->get()
            ->map(fn ($row) => [
                'type'            => $row->type,
                'label'           => SocialMediaPostTypeEnum::labels()[$row->type] ?? $row->type,
                'posts'           => (int) $row->posts,
                'reach'           => (int) $row->reach,
                'average_reach'   => (int) round($row->reach / max(1, $row->posts)),
                'engagements'     => (int) $row->engagements,
                'saves'           => (int) $row->saves,
                'shares'          => (int) $row->shares,
                'engagement_rate' => $this->rate((int) $row->engagements, (int) $row->reach),
            ])
            ->sortByDesc('engagement_rate')
            ->values()
            ->all();
    }

    /**
     * @param Collection<int, Shop> $shops
     *
     * @return array<int, array<string, mixed>>
     */
    private function topPosts(Collection $shops, ?SocialMediaPlatformEnum $platform, ?Carbon $from, ?Carbon $to): array
    {
        return $this->posts($shops, $platform, $from, $to)
            ->join('shops as s', 's.id', '=', 'a.shop_id')
            ->selectRaw('p.id, p.type, p.description, p.permalink, p.published_at, p.reach, p.impressions, p.saves, p.shares, p.comments, p.reactions, p.link_clicks, '.self::ENGAGEMENTS.' as engagements, a.name as account_name, a.platform, s.name as shop_name')
            ->orderByRaw(self::ENGAGEMENTS.' DESC')
            ->orderByDesc('p.reach')
            ->limit(self::TOP_POSTS)
            ->get()
            ->map(fn ($post) => [
                'id'              => $post->id,
                'description'     => $post->description ? mb_strimwidth($post->description, 0, 120, '…') : null,
                'permalink'       => $post->permalink,
                'published_at'    => Carbon::parse($post->published_at)->toIso8601String(),
                'type'            => $post->type,
                'type_label'      => SocialMediaPostTypeEnum::labels()[$post->type] ?? $post->type,
                'platform'        => $post->platform,
                'platform_label'  => SocialMediaPlatformEnum::labels()[$post->platform] ?? $post->platform,
                'account_name'    => $post->account_name,
                'shop_name'       => $post->shop_name,
                'reach'           => (int) $post->reach,
                'impressions'     => (int) $post->impressions,
                'reactions'       => (int) $post->reactions,
                'comments'        => (int) $post->comments,
                'shares'          => (int) $post->shares,
                'saves'           => (int) $post->saves,
                'link_clicks'     => (int) $post->link_clicks,
                'engagements'     => (int) $post->engagements,
                'engagement_rate' => $this->rate((int) $post->engagements, (int) $post->reach),
            ])
            ->all();
    }

    /**
     * @param Collection<int, Shop> $shops
     *
     * @return array<int, array<string, mixed>>
     */
    private function accounts(Collection $shops, ?SocialMediaPlatformEnum $platform, ?Carbon $from, ?Carbon $to): array
    {
        $posts = $this->posts($shops, $platform, $from, $to)
            ->groupBy('a.id')
            ->selectRaw('a.id, COUNT(*) as posts, SUM(p.reach) as reach, SUM('.self::ENGAGEMENTS.') as engagements, SUM(p.link_clicks) as link_clicks')
            ->get()
            ->keyBy('id');

        $shopsById = $shops->keyBy('id');

        return $this->accountsQuery($shops, $platform)
            ->select('a.id', 'a.name', 'a.username', 'a.platform', 'a.shop_id', 'a.followers', 'a.followers_at')
            ->get()
            ->map(function ($account) use ($posts, $shopsById) {
                $row  = $posts[$account->id] ?? null;
                $shop = $shopsById[$account->shop_id];

                return [
                    'id'              => $account->id,
                    'name'            => $account->name,
                    'username'        => $account->username,
                    'platform'        => $account->platform,
                    'platform_label'  => SocialMediaPlatformEnum::labels()[$account->platform] ?? $account->platform,
                    'shop_name'       => $shop->name,
                    'shop_slug'       => $shop->slug,
                    'country_code'    => $shop->country?->code,
                    'followers'       => $account->followers !== null ? (int) $account->followers : null,
                    'followers_at'    => $account->followers_at,
                    'posts'           => (int) ($row->posts ?? 0),
                    'reach'           => (int) ($row->reach ?? 0),
                    'engagements'     => (int) ($row->engagements ?? 0),
                    'link_clicks'     => (int) ($row->link_clicks ?? 0),
                    'engagement_rate' => $this->rate((int) ($row->engagements ?? 0), (int) ($row->reach ?? 0)),
                ];
            })
            ->sortByDesc('reach')
            ->values()
            ->all();
    }

    /**
     * @param Collection<int, Shop> $shops
     *
     * @return array{shops: array<int, array{slug: string, name: string, country_code: string|null, has_accounts: bool}>, countries: array<int, array{code: string, name: string}>, platforms: array<int, array{value: string, label: string}>}
     */
    private function options(Collection $shops): array
    {
        $withAccounts = DB::table('social_media_accounts')->whereIn('shop_id', $shops->pluck('id'))->distinct()->pluck('shop_id')->flip();

        return [
            'shops'     => $shops->map(fn (Shop $shop) => [
                'id'           => $shop->id,
                'slug'         => $shop->slug,
                'name'         => $shop->name,
                'country_code' => $shop->country?->code,
                'has_accounts' => isset($withAccounts[$shop->id]),
            ])->values()->all(),
            'countries' => $shops->pluck('country')->filter()->unique('id')->sortBy('name')
                ->map(fn ($country) => ['code' => $country->code, 'name' => $country->name])->values()->all(),
            'platforms' => collect(SocialMediaPlatformEnum::labels())
                ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])->values()->all(),
        ];
    }

    /**
     * @param Collection<int, Shop> $shops
     */
    private function dataUntil(Collection $shops): ?string
    {
        $accountIds = DB::table('social_media_accounts')->whereIn('shop_id', $shops->pluck('id'))->pluck('id');

        $latest = collect([
            DB::table('social_media_posts')->whereIn('social_media_account_id', $accountIds)->max('published_at'),
            DB::table('social_media_account_daily_stats')->whereIn('social_media_account_id', $accountIds)->max('date'),
        ])->filter()->map(fn ($date) => Carbon::parse($date))->max();

        return $latest?->toDateString();
    }
}
