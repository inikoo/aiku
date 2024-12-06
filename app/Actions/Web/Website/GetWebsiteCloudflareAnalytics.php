<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 01-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\Website;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Web\Website;
use Arr;
use Carbon\Carbon;
use Exception;
use Http;
use Illuminate\Support\Facades\Date;
use Lorisleiva\Actions\Concerns\AsAction;

use function Amp\async;
use function Amp\Future\await;

class GetWebsiteCloudflareAnalytics extends OrgAction
{
    use AsAction;
    use WithNoStrictRules;

    private Website $website;
    /**
     * @throws \Throwable
     */
    public function handle(Website $website, array $modelData): array
    {

        $groupSettings = $website->group->settings;
        $dataWebsite = $website->data;

        $apiToken = Arr::get($groupSettings, 'cloudflare.apiToken');
        $zoneTag = Arr::get($dataWebsite, "cloudflare.zoneTag");
        $accountTag = Arr::get($dataWebsite, "cloudflare.accountTag");
        $siteTag = Arr::get($dataWebsite, "cloudflare.siteTag");

        data_set($modelData, "zoneTag", $zoneTag);
        data_set($modelData, "accountTag", $accountTag);
        data_set($modelData, "siteTag", $siteTag);
        data_set($modelData, "apiToken", $apiToken);

        $queryAnalyticTopNs = $this->getQueryAnalyticTopNs($modelData);
        $queryRumSparklineBydatetimeGroupByAll = $this->getQueryRumSparklineBydatetimeGroupByAll($modelData);
        $queryZone = $this->getQueryZone($zoneTag, $modelData);

        $cacheKey = "cloudflare_analytics_{$website->id}_" . md5(json_encode($modelData));
        $cacheTTL = now()->addMinutes(30); // Cache for 30 minutes

        $cachedData = cache()->get($cacheKey);

        if ($cachedData) {
            return $cachedData;
        }

        $topPromise = $queryAnalyticTopNs ? async(fn () => $this->getCloudflareAnalytics($modelData, $queryAnalyticTopNs)) : [];
        $rumGroupAllPromise = $queryRumSparklineBydatetimeGroupByAll ? async(fn () => $this->getCloudflareAnalytics($modelData, $queryRumSparklineBydatetimeGroupByAll)) : [];
        $zonePromise = async(fn () => $this->getCloudflareAnalytics($modelData, $queryZone));

        [$top, $zone, $rumGroupAll] = await([$topPromise, $zonePromise, $rumGroupAllPromise]);

        $data = [
            'top' => $top,
            'zone' => $zone,
            'rumGroupAll' => $rumGroupAll,
        ];

        cache()->put($cacheKey, $data, $cacheTTL);

        return $data;
    }

    private function getCloudflareAnalytics(array $modelData, string $query, $try = 3): array
    {
        $apiToken = Arr::get($modelData, "apiToken");

        $urlCLoudflareGraphql = "https://api.cloudflare.com/client/v4/graphql";
        if ($try == 0) {
            return [];
        }

        try {
            $response = Http::timeout(10)->withHeaders([
                'Authorization' => "Bearer $apiToken",
                'Content-Type' => 'application/json',
            ])->post($urlCLoudflareGraphql, [
                'query' => $query,
            ]);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Resolving timed out')) {
                return $this->getCloudflareAnalytics($modelData, $query, --$try);
            }
            throw $e;
        }


        return $response->json();
    }

    private function isIso8601($dateString)
    {
        try {
            $date = Carbon::parse($dateString);
            return $date->toIso8601String() === $dateString;
        } catch (Exception $e) {
            return false;
        }
    }

    private function isDate($dateString)
    {
        try {
            $date = Carbon::parse($dateString);
            return $date->toDateString() === $dateString;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getQueryZone($zoneTag, $modelData): string
    {
        $since = Arr::get($modelData, 'since') ?? Date::now()->subDay()->toIso8601String();
        $until = Arr::get($modelData, 'until') ?? Date::now()->toIso8601String();

        $timeField = 'datetime';
        $groupsFunction = 'httpRequests1hGroups';
        $orderBy = 'datetime_ASC';

        if ($this->isDate($since) && $this->isDate($until)) {
            $timeField = 'date';
            $groupsFunction = 'httpRequests1dGroups';
            $orderBy = 'date_ASC';
        }
        return <<<GQL
        query Viewer {
            viewer {
                zones(filter: { zoneTag: "$zoneTag" }) {
                    totals: $groupsFunction(
                        limit: 10000
                        filter: { {$timeField}_geq: "$since", {$timeField}_lt: "$until" }
                    ) {
                        uniq {
                            uniques
                        }
                    }
                    zones: $groupsFunction(
                        orderBy: [$orderBy]
                        limit: 10000
                        filter: { {$timeField}_geq: "$since", {$timeField}_lt: "$until" }
                    ) {
                        dimensions {
                            timeslot: $timeField
                        }
                        uniq {
                            uniques
                        }
                        sum {
                            browserMap {
                                pageViews
                                key: uaBrowserFamily
                            }
                            pageViews
                            requests
                        }
                    }
                }
            }
        }
    GQL;
    }

    private function getQueryAnalyticTopNs($modelData): ?string
    {
        $siteTag = Arr::get($modelData, 'siteTag');
        $accountTag = Arr::get($modelData, 'accountTag');
        $since = Arr::get($modelData, 'since') ?? Date::now()->subDay()->toIso8601String();
        $until = Arr::get($modelData, 'until') ?? Date::now()->toIso8601String();

        if (!$siteTag || !$accountTag) {
            return null;
        }

        $filter = <<<FILTER
        {
            AND: [
                { datetime_geq: "$since", datetime_leq: "$until" },
                { OR: [{ siteTag: "$siteTag" }] },
                { bot: 0 }
            ]
        }
        FILTER;

        return <<<GQL
        query Viewer {
            viewer {
                accounts(filter: {accountTag: "$accountTag"}) {
                    visits: rumPageloadEventsAdaptiveGroups(limit: 5000, filter: $filter) {
                        sum {
                            visits
                        }
                        avg {
                            sampleInterval
                        }
                        dimensions {
                            ts: datetimeFifteenMinutes
                        }
                    }
                    pageviews: rumPageloadEventsAdaptiveGroups(limit: 5000, filter: $filter) {
                        count
                        avg {
                            sampleInterval
                        }
                        dimensions {
                            ts: datetimeFifteenMinutes
                        }
                    }
                    performance: rumPerformanceEventsAdaptiveGroups(limit: 5000, filter: $filter) {
                        count
                        aggregation: quantiles {
                            pageLoadTime: pageLoadTimeP50
                        }
                        avg {
                            sampleInterval
                        }
                        dimensions {
                            ts: datetimeFifteenMinutes
                        }
                    }
                    totalPerformance: rumPerformanceEventsAdaptiveGroups(limit: 1, filter: $filter) {
                        aggregation: quantiles {
                            pageLoadTime: pageLoadTimeP50
                        }
                    }
                    lcp: rumWebVitalsEventsAdaptiveGroups(filter: $filter, limit: 1) {
                        count
                        sum {
                            lcpTotal
                            lcpGood
                            lcpNeedsImprovement
                            lcpPoor
                        }
                        avg {
                            sampleInterval
                        }
                    }
                    inp: rumWebVitalsEventsAdaptiveGroups(filter: $filter, limit: 1) {
                        count
                        sum {
                            inpTotal
                            inpGood
                            inpNeedsImprovement
                            inpPoor
                        }
                        avg {
                            sampleInterval
                        }
                    }
                    fid: rumWebVitalsEventsAdaptiveGroups(filter: $filter, limit: 1) {
                        count
                        sum {
                            fidTotal
                            fidGood
                            fidNeedsImprovement
                            fidPoor
                        }
                        avg {
                            sampleInterval
                        }
                    }
                    cls: rumWebVitalsEventsAdaptiveGroups(filter: $filter, limit: 1) {
                        count
                        sum {
                            clsTotal
                            clsGood
                            clsNeedsImprovement
                            clsPoor
                        }
                        avg {
                            sampleInterval
                        }
                    }
                    visitsDelta: rumPageloadEventsAdaptiveGroups(limit: 1, filter: $filter) {
                        sum {
                            visits
                        }
                    }
                    pageviewsDelta: rumPageloadEventsAdaptiveGroups(limit: 1, filter: $filter) {
                        count
                    }
                    performanceDelta: rumPerformanceEventsAdaptiveGroups(limit: 1, filter: $filter) {
                        count
                        aggregation: quantiles {
                            pageLoadTime: pageLoadTimeP50
                        }
                    }
                }
            }
        }
        GQL;
    }

    private function getQueryRumSparklineBydatetimeGroupByAll($modelData): ?string
    {
        $siteTag = Arr::get($modelData, 'siteTag');
        $accountTag = Arr::get($modelData, 'accountTag');
        $since = Arr::get($modelData, 'since') ?? Date::now()->subDay()->toIso8601String();
        $until = Arr::get($modelData, 'until') ?? Date::now()->toIso8601String();

        if (!$siteTag || !$accountTag) {
            return null;
        }

        $filter = <<<FILTER
        {
            AND: [
                { datetime_geq: "$since", datetime_leq: "$until" },
                { OR: [{ siteTag: "$siteTag" }] },
                { bot: 0 }
            ]
        }
        FILTER;

        return <<<GQL
        query Viewer {
            viewer {
                accounts(filter: { accountTag: "$accountTag" }) {
                series: rumPageloadEventsAdaptiveGroups(limit: 10000, filter: $filter) {
                    count
                    avg {
                        sampleInterval
                    }
                    sum {
                    visits
                    }
                    dimensions {
                        ts: datetimeFifteenMinutes
                    }
                }
                }
            }
        }
        GQL;
    }

    public function rules(): array
    {
        return [
            'since' => ['sometimes', 'required'],
            'until' => ['sometimes', 'required']
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $since = $this->since;
            $until = $this->until;
            if (isset($since) && !$this->isIso8601($since) && !$this->isDate($since)) {
                $validator->errors()->add('since', 'The since field must be a valid ISO 8601 or YYYY-MM-DD date.');
            }

            if (isset($until) && !$this->isIso8601($until) && !$this->isDate($until)) {
                $validator->errors()->add('until', 'The until field must be a valid ISO 8601 or YYYY-MM-DD date.');
            }
        });
    }

    public function action(Website $website, array $modelData, bool $strict = true): array
    {
        $this->strict   = $strict;
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($website, $validatedData);
    }


    // public string $commandSignature = "xxx";

    // /**
    //  * @throws \Exception
    //  */
    // public function asCommand($command): int
    // {
    //     if ($command->argument("website")) {
    //         try {
    //             /** @var Website $website */
    //             $website = Website::where("slug", $command->argument("website"))->firstOrFail();
    //         } catch (Exception) {
    //             $command->error("Website not found");
    //             exit();
    //         }

    //         $this->action($website, []);

    //         $command->line("Website ".$website->slug." fetched");

    //     } else {
    //         foreach (Website::orderBy('id')->get() as $website) {
    //             $command->line("Website ".$website->slug." fetched");
    //             $this->action($website, []);
    //         }
    //     }


    //     return 0;
    // }


}
