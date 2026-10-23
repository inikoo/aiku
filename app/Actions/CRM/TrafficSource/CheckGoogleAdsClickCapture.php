<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Fri, 23 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\CRM\TrafficSource;

use App\Actions\SysAdmin\User\NotifyUserOnChosenChannels;
use App\Enums\CRM\TrafficSource\TrafficSourcesTypeEnum;
use App\Enums\SysAdmin\User\UserNotificationEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Permission;
use App\Models\SysAdmin\User;
use App\Notifications\GoogleAdsClickCaptureDropNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckGoogleAdsClickCapture
{
    use AsAction;

    public const float MINIMUM_CAPTURE_RATE = 0.85;

    /**
     * Below this many paid clicks a day a handful of lost clicks swings the rate past the threshold.
     */
    public const int MINIMUM_GOOGLE_CLICKS = 20;

    public string $commandSignature = 'traffic-source:check-google-ads-click-capture {--date= : Day to check, defaults to yesterday}';

    public string $commandDescription = 'Compare the clicks Google Ads reports per shop with the clicks Aiku recorded with their click id';

    /**
     * @return Collection<int, array{shop_id: int, shop: string, google_clicks: int, captured_clicks: int, capture_rate: float, url: string}>
     */
    public function handle(Carbon $date): Collection
    {
        $googleClicks = DB::table('traffic_source_campaign_metrics as metrics')
            ->join('traffic_source_campaigns as campaigns', 'campaigns.id', '=', 'metrics.traffic_source_campaign_id')
            ->join('traffic_sources', 'traffic_sources.id', '=', 'campaigns.traffic_source_id')
            ->where('traffic_sources.type', TrafficSourcesTypeEnum::GOOGLE_ADS->value)
            ->whereDate('metrics.date', $date->toDateString())
            ->groupBy('traffic_sources.shop_id')
            ->selectRaw('traffic_sources.shop_id, SUM(metrics.clicks) AS clicks')
            ->pluck('clicks', 'shop_id');

        return Shop::whereIn('id', $googleClicks->keys())
            ->with(['timezone', 'organisation'])
            ->get()
            ->map(function (Shop $shop) use ($googleClicks, $date) {
                $googleClickCount   = (int) $googleClicks[$shop->id];
                $capturedClickCount = $this->capturedClicks($shop, $date);

                return [
                    'shop_id'         => $shop->id,
                    'shop'            => $shop->name,
                    'google_clicks'   => $googleClickCount,
                    'captured_clicks' => $capturedClickCount,
                    'capture_rate'    => $googleClickCount > 0 ? $capturedClickCount / $googleClickCount : 1.0,
                    'url'             => route('grp.org.shops.show.marketing.google_ads.index', [$shop->organisation->slug, $shop->slug]),
                ];
            })
            ->sortBy('capture_rate')
            ->values();
    }

    private function capturedClicks(Shop $shop, Carbon $date): int
    {
        $dayStart = Carbon::parse($date->toDateString(), $shop->timezoneName())->utc();

        return (int) DB::table('traffic_source_clicks')
            ->where('shop_id', $shop->id)
            ->where('type', TrafficSourcesTypeEnum::GOOGLE_ADS->value)
            ->where('is_bot', false)
            ->whereNotNull('click_id')
            ->where('created_at', '>=', $dayStart)
            ->where('created_at', '<', $dayStart->copy()->addDay())
            ->distinct()
            ->count('click_id');
    }

    /**
     * @param Collection<int, array{shop_id: int, shop: string, google_clicks: int, captured_clicks: int, capture_rate: float, url: string}> $dropped
     */
    public function notify(Collection $dropped, Carbon $date): int
    {
        $shopIdsByPermission = $dropped->pluck('shop_id')->mapWithKeys(fn (int $shopId) => ["marketing.$shopId.edit" => $shopId]);
        $permissions         = Permission::whereIn('name', $shopIdsByPermission->keys())->pluck('name', 'id');

        if ($permissions->isEmpty()) {
            return 0;
        }

        $throughRoles = DB::table('model_has_roles')
            ->join('role_has_permissions', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', 'User')
            ->whereIn('role_has_permissions.permission_id', $permissions->keys())
            ->select('model_has_roles.model_id', 'role_has_permissions.permission_id');

        $shopIdsByUser = DB::table('model_has_permissions')
            ->where('model_type', 'User')
            ->whereIn('permission_id', $permissions->keys())
            ->select('model_id', 'permission_id')
            ->union($throughRoles)
            ->get()
            ->groupBy('model_id')
            ->map(fn (Collection $rows) => $rows->map(fn (object $row) => $shopIdsByPermission[$permissions[$row->permission_id]])->unique());

        $users = User::whereIn('id', $shopIdsByUser->keys())->where('status', true)->get();

        foreach ($users as $user) {
            $shops        = $dropped->whereIn('shop_id', $shopIdsByUser[$user->id])->values()->all();
            $notification = new GoogleAdsClickCaptureDropNotification($shops, $date->toDateString());

            NotifyUserOnChosenChannels::run(
                $user,
                UserNotificationEnum::GOOGLE_ADS_CLICK_CAPTURE_DROP,
                $notification,
                $notification->title(),
                $notification->summary(),
                $shops[0]['url']
            );
        }

        return $users->count();
    }

    public function asCommand(Command $command): int
    {
        $date    = Carbon::parse($command->option('date') ?? now()->subDay()->toDateString());
        $results = $this->handle($date);

        $command->table(
            ['Shop', 'Google clicks', 'Recorded with click id', 'Rate'],
            $results->map(fn (array $result) => [
                $result['shop'],
                $result['google_clicks'],
                $result['captured_clicks'],
                round($result['capture_rate'] * 100).'%',
            ])
        );

        $dropped = $results
            ->where('google_clicks', '>=', self::MINIMUM_GOOGLE_CLICKS)
            ->where('capture_rate', '<', self::MINIMUM_CAPTURE_RATE)
            ->values();

        if ($dropped->isNotEmpty()) {
            $command->info('Notified '.$this->notify($dropped, $date).' user(s) about '.$dropped->count().' shop(s).');
        }

        return Command::SUCCESS;
    }
}
