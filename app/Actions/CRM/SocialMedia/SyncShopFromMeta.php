<?php

/*
 * Author: Andi Ferdiawan <andiferdiawan22@gmail.com>
 * Created: Wed, 21 Oct 2026
 * Copyright (c) 2026, Andi Ferdiawan
 */

namespace App\Actions\CRM\SocialMedia;

use App\Enums\CRM\TrafficSource\TrafficSourcesTypeEnum;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * A brand's Meta figures pulled now rather than at the nightly run: the organic sync for its
 * Facebook Page and Instagram account, and the Meta Ads spend for its ad account, whichever of the
 * two the brand is set up for. It runs on the queue, one at a time per brand and not again within
 * a few minutes of the last, so a button pressed twice does not spend Meta's rate limit twice.
 */
class SyncShopFromMeta
{
    use AsAction;

    public const int ORGANIC_DAYS  = 28;
    public const int PAID_DAYS     = 7;
    public const int COOLDOWN_MINS = 10;

    public const int RUNNING_MINS = 20;

    public string $jobQueue = 'default';

    public int $jobTimeout = 1200;

    public int $jobTries = 1;

    public function handle(Shop $shop): array
    {
        $results = [];

        try {
            if (self::hasOrganic($shop)) {
                $results['organic'] = $this->runCommand('social-media:fetch-meta', ['shop' => $shop->slug, '--days' => self::ORGANIC_DAYS]);
            }

            if (self::hasPaid($shop)) {
                $results['paid'] = $this->runCommand('traffic-source:fetch-meta-costs', ['shop' => $shop->slug, '--days' => self::PAID_DAYS]);
            }
        } finally {
            Cache::forget(self::runningKey($shop));
        }

        $result = [
            'finished_at' => now()->toIso8601String(),
            'ok'          => collect($results)->every(fn (array $result) => $result['ok']),
            'message'     => collect($results)->map(fn (array $result, string $part) => $result['ok'] ? null : $part.': '.$result['message'])->filter()->implode(' · ') ?: null,
        ];

        Cache::put(self::resultKey($shop), $result, now()->addDays(30));

        return $result;
    }

    public function authorize(ActionRequest $request): bool
    {
        $shop = $request->route('shop');

        return $shop instanceof Shop && $request->user()->authTo("marketing.$shop->id.edit");
    }

    /**
     * @throws ValidationException
     */
    public function asController(Shop $shop, ActionRequest $request): array
    {
        if (!self::hasOrganic($shop) && !self::hasPaid($shop)) {
            throw ValidationException::withMessages(['shop' => __('This brand is not set up for Meta yet: add its Facebook Page ID, Instagram account ID or Meta Ads account in Edit shop')]);
        }

        $last = Cache::get(self::resultKey($shop));
        if ($last && Carbon::parse($last['finished_at'])->gt(now()->subMinutes(self::COOLDOWN_MINS))) {
            throw ValidationException::withMessages(['shop' => __('This brand was synced a few minutes ago. Try again after :time.', ['time' => Carbon::parse($last['finished_at'])->addMinutes(self::COOLDOWN_MINS)->format('H:i')])]);
        }

        if (!Cache::add(self::runningKey($shop), now()->toIso8601String(), now()->addMinutes(self::RUNNING_MINS))) {
            throw ValidationException::withMessages(['shop' => __('This brand is already syncing')]);
        }

        self::dispatch($shop);

        return self::status($shop);
    }

    /**
     * What the dashboard tells a user about a brand's Meta connection: what it is set up for, whether
     * a sync is running, how the last one ended, and when its figures last changed, whether a sync,
     * the nightly run or an upload changed them.
     *
     * @return array{organic: bool, paid: bool, running: bool, last_sync: array{finished_at: string, ok: bool, message: string|null}|null, organic_updated_at: string|null, paid_updated_at: string|null}
     */
    public static function status(Shop $shop): array
    {
        $accountIds = $shop->socialMediaAccounts()->pluck('id');

        $organicUpdatedAt = collect([
            DB::table('social_media_account_daily_stats')->whereIn('social_media_account_id', $accountIds)->max('updated_at'),
            DB::table('social_media_posts')->whereIn('social_media_account_id', $accountIds)->max('updated_at'),
        ])->filter()->map(fn ($date) => Carbon::parse($date))->max();

        $paidUpdatedAt = DB::table('traffic_source_costs as c')
            ->join('traffic_sources as s', 's.id', '=', 'c.traffic_source_id')
            ->where('s.shop_id', $shop->id)
            ->whereIn('s.type', [TrafficSourcesTypeEnum::META_ADS->value, TrafficSourcesTypeEnum::INSTAGRAM_ADS->value])
            ->max('c.updated_at');

        return [
            'organic'            => self::hasOrganic($shop),
            'paid'               => self::hasPaid($shop),
            'running'            => Cache::has(self::runningKey($shop)),
            'last_sync'          => Cache::get(self::resultKey($shop)),
            'organic_updated_at' => $organicUpdatedAt?->toIso8601String(),
            'paid_updated_at'    => $paidUpdatedAt ? Carbon::parse($paidUpdatedAt)->toIso8601String() : null,
        ];
    }

    public static function hasOrganic(Shop $shop): bool
    {
        return filled(data_get($shop->settings, 'meta_social.facebook_page_id')) || filled(data_get($shop->settings, 'meta_social.instagram_account_id'));
    }

    public static function hasPaid(Shop $shop): bool
    {
        return filled(data_get($shop->settings, 'meta_ads.ad_account_id'));
    }

    /**
     * @return array{ok: bool, message: string|null}
     */
    private function runCommand(string $command, array $arguments): array
    {
        try {
            $exitCode = Artisan::call($command, $arguments);
            $output   = trim(Artisan::output());
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $this->withoutToken($e->getMessage())];
        }

        return [
            'ok'      => $exitCode === 0,
            'message' => $exitCode === 0 ? null : $this->withoutToken((string) collect(explode("\n", $output))->filter()->last()),
        ];
    }

    private function withoutToken(string $message): string
    {
        return mb_strimwidth((string) preg_replace('/access_token=[^&\s"\']+/', 'access_token=***', $message), 0, 300, '…');
    }

    private static function runningKey(Shop $shop): string
    {
        return 'social-media-sync:running:'.$shop->id;
    }

    private static function resultKey(Shop $shop): string
    {
        return 'social-media-sync:result:'.$shop->id;
    }
}
