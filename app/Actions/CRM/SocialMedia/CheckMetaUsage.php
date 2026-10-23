<?php

/*
 * Author: Andi Ferdiawan <andiferdiawan22@gmail.com>
 * Created: Wed, 21 Oct 2026
 * Copyright (c) 2026, Andi Ferdiawan
 */

namespace App\Actions\CRM\SocialMedia;

use App\Exceptions\MetaUsageLimitReached;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Meta reports with every response how much of its rate limit the app, the Page or Instagram
 * account and the ad account have used, as percentages. A run stops well before any of them is
 * spent, and at once when Meta answers with one of its throttling errors, so a sync never pushes
 * on into a temporary block of the brand's assets.
 */
class CheckMetaUsage
{
    use AsObject;

    public const int STOP_AT_PERCENT = 80;

    /**
     * Meta's error codes for an app, user, Page, ad account or business use case over its limit.
     */
    public const array THROTTLING_CODES = [4, 17, 32, 613, 80000, 80001, 80002, 80003, 80004, 80005, 80006, 80008, 80009, 80014];

    /**
     * @throws MetaUsageLimitReached
     */
    public function handle(Response $response): void
    {
        if (in_array((int) data_get($response->json(), 'error.code'), self::THROTTLING_CODES, true)) {
            throw MetaUsageLimitReached::throttled($this->minutesToWait($response));
        }

        $usage = $this->usageByLimit($response);
        $limit = (string) $usage->sortDesc()->keys()->first();

        if ($limit !== '' && $usage[$limit] >= self::STOP_AT_PERCENT) {
            throw MetaUsageLimitReached::make($usage[$limit], $this->minutesToWait($response), $limit);
        }
    }

    public function highestUsage(Response $response): int
    {
        return (int) $this->usageByLimit($response)->max();
    }

    /**
     * How much of each limit Meta reported is used, in percent: the app's own hourly calls, the ad
     * account, the ads insights throttle and each business use case (pages, instagram, ads insights).
     *
     * @return Collection<string, int>
     */
    public function usageByLimit(Response $response): Collection
    {
        $app      = (array) json_decode((string) $response->header('X-App-Usage'), true);
        $adUsage  = (array) json_decode((string) $response->header('X-Ad-Account-Usage'), true);
        $insights = (array) json_decode((string) $response->header('X-FB-Ads-Insights-Throttle'), true);

        $business = collect((array) json_decode((string) $response->header('X-Business-Use-Case-Usage'), true))
            ->flatten(1)
            ->filter(fn ($usage) => is_array($usage))
            ->mapWithKeys(fn (array $usage) => ['use case '.data_get($usage, 'type', 'business') => max((int) data_get($usage, 'call_count', 0), (int) data_get($usage, 'total_cputime', 0), (int) data_get($usage, 'total_time', 0))]);

        return collect([
            'app calls'          => collect([data_get($app, 'call_count'), data_get($app, 'total_cputime'), data_get($app, 'total_time')])->filter(fn ($value) => is_numeric($value))->max(),
            'ad account'         => data_get($adUsage, 'acc_id_util_pct'),
            'ads insights (app)' => data_get($insights, 'app_id_util_pct'),
            'ads insights'       => data_get($insights, 'acc_id_util_pct'),
        ])->merge($business)
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value);
    }

    private function minutesToWait(Response $response): ?int
    {
        $minutes = collect((array) json_decode((string) $response->header('X-Business-Use-Case-Usage'), true))
            ->flatten(1)
            ->map(fn ($usage) => (int) data_get($usage, 'estimated_time_to_regain_access', 0))
            ->max();

        return $minutes ?: null;
    }
}
