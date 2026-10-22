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
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * A brand's Meta figures pulled now rather than at the nightly run: the organic sync for its
 * Facebook Page and Instagram account, and the Meta Ads spend for its ad account, whichever of the
 * two the brand is set up for. It runs on the queue, one brand at a time across the installation
 * and not again within a few minutes of the last, so pressing the buttons of many brands, or one
 * button twice, does not spend Meta's rate limit in a burst. A brand's first sync reaches back
 * further, up to MAX_DAYS, to fill its history; later ones refresh the recent days unless the user
 * picks a range, never longer than MAX_DAYS.
 */
class SyncShopFromMeta
{
    use AsAction;

    public const int ORGANIC_DAYS  = 28;
    public const int PAID_DAYS     = 7;
    public const int MAX_DAYS      = 90;
    public const int COOLDOWN_MINS = 10;

    public const array DAY_CHOICES = [7, 28, self::MAX_DAYS];

    public const int RUNNING_MINS = 120;

    public string $jobQueue = 'default';

    public int $jobTimeout = 1800;

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping('social-media-sync'))->releaseAfter(60)->expireAfter($this->jobTimeout + 60)];
    }

    public function getJobRetryUntil(): Carbon
    {
        return now()->addMinutes(self::RUNNING_MINS);
    }

    public function handle(Shop $shop, ?int $days = null): array
    {
        $results = [];
        $status  = self::status($shop);

        try {
            if (self::hasOrganic($shop)) {
                $results['organic'] = $this->runCommand('social-media:fetch-meta', ['shop' => $shop->slug, '--days' => $days ?? ($status['organic_updated_at'] ? self::ORGANIC_DAYS : self::MAX_DAYS)]);
            }

            if (self::hasPaid($shop)) {
                $results['paid'] = $this->runCommand('traffic-source:fetch-meta-costs', ['shop' => $shop->slug, '--days' => $days ?? ($status['paid_updated_at'] ? self::PAID_DAYS : self::MAX_DAYS)]);
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

    public function rules(): array
    {
        return [
            'days' => ['nullable', 'integer', Rule::in(self::DAY_CHOICES)],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function asController(Shop $shop, ActionRequest $request): array
    {
        if (!self::hasOrganic($shop) && !self::hasPaid($shop)) {
            throw ValidationException::withMessages(['shop' => __('This brand is not set up for Meta yet: add its Facebook Page ID, Instagram account ID or Meta Ads account in Edit shop')]);
        }

        if ($availableAt = self::availableAt($shop)) {
            throw ValidationException::withMessages(['shop' => trans_choice('This brand was synced a few minutes ago. Try again in :count minute.|This brand was synced a few minutes ago. Try again in :count minutes.', $minutes = (int) ceil(now()->diffInSeconds($availableAt) / 60), ['count' => $minutes])]);
        }

        if (!Cache::add(self::runningKey($shop), now()->toIso8601String(), now()->addMinutes(self::RUNNING_MINS))) {
            throw ValidationException::withMessages(['shop' => __('This brand is already syncing')]);
        }

        self::dispatch($shop, $request->validated('days'));

        return self::status($shop);
    }

    /**
     * What the dashboard tells a user about a brand's Meta connection: what it is set up for, whether
     * a sync is running, how the last one ended, and when its figures last changed, whether a sync,
     * the nightly run or an upload changed them.
     *
     * @return array{organic: bool, paid: bool, running: bool, last_sync: array{finished_at: string, ok: bool, message: string|null}|null, available_at: string|null, first_sync: bool, organic_updated_at: string|null, paid_updated_at: string|null}
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
            'available_at'       => self::availableAt($shop)?->toIso8601String(),
            'first_sync'         => (self::hasOrganic($shop) && !$organicUpdatedAt) || (self::hasPaid($shop) && !$paidUpdatedAt),
            'organic_updated_at' => $organicUpdatedAt?->toIso8601String(),
            'paid_updated_at'    => $paidUpdatedAt ? Carbon::parse($paidUpdatedAt)->toIso8601String() : null,
        ];
    }

    /**
     * When a brand synced a few minutes ago may sync again; null once it may.
     */
    public static function availableAt(Shop $shop): ?Carbon
    {
        $finishedAt = data_get(Cache::get(self::resultKey($shop)), 'finished_at');
        $availableAt = $finishedAt ? Carbon::parse($finishedAt)->addMinutes(self::COOLDOWN_MINS) : null;

        return $availableAt?->isFuture() ? $availableAt : null;
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
