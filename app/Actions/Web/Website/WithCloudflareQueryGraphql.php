<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 09-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\Website;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;

trait WithCloudflareQueryGraphql
{
    public function getZone($modelData): array
    {
        $since = Arr::get($modelData, 'since') ?? Date::now()->subDay()->toIso8601String();
        $until = Arr::get($modelData, 'until') ?? Date::now()->toIso8601String();
        $zoneTag = Arr::get($modelData, 'zoneTag');

        $timeField = 'datetime';
        $groupsFunction = 'httpRequests1hGroups';
        $orderBy = 'datetime_ASC';

        if ($this->isDate($since) && $this->isDate($until)) {
            $timeField = 'date';
            $groupsFunction = 'httpRequests1dGroups';
            $orderBy = 'date_ASC';
        }
        $query = <<<GQL
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

        return $this->getCloudflareAnalytics($modelData, $query);
    }

    public function getRumAnalyticsTopNs($modelData): array
    {
        $show = Arr::get($modelData, 'showTopNs') ?? 'visits';
        $siteTag = Arr::get($modelData, 'siteTag');
        $accountTag = Arr::get($modelData, 'accountTag');
        $since = Arr::get($modelData, 'since') ?? Date::now()->subDay()->toIso8601String();
        $until = Arr::get($modelData, 'until') ?? Date::now()->toIso8601String();

        if (!$siteTag || !$accountTag) {
            return [];
        }

        $order = 'count_DESC';
        if ($show === 'visits') {
            $order = 'sum_visits_DESC';
        }

        // Define the filter as a variable
        $filter = <<<FILTER
            {
                AND: [
                    { datetime_geq: "$since", datetime_leq: "$until" },
                    { OR: [{ siteTag: "$siteTag" }] },
                    { bot: 0 }
                ]
            }
        FILTER;

        // Return the query
        $query = <<<GQL
            query Viewer {
                viewer {
                    accounts(filter: { accountTag: "$accountTag" }) {
                        total: rumPageloadEventsAdaptiveGroups(
                            filter: $filter, 
                            limit: 1
                        ) {
                            count
                            sum {
                                visits
                            }
                        }
                        topReferers: rumPageloadEventsAdaptiveGroups(
                            filter: $filter, 
                            limit: 15, 
                            orderBy: [$order]
                        ) {
                            count
                            avg {
                                sampleInterval
                            }
                            sum {
                                visits
                            }
                            dimensions {
                                metric: refererHost
                            }
                        }
                        topPaths: rumPageloadEventsAdaptiveGroups(
                            filter: $filter, 
                            limit: 15, 
                            orderBy: [$order]
                        ) {
                            count
                            avg {
                                sampleInterval
                            }
                            sum {
                                visits
                            }
                            dimensions {
                                metric: requestPath
                            }
                        }
                        topHosts: rumPageloadEventsAdaptiveGroups(
                            filter: $filter, 
                            limit: 15, 
                            orderBy: [$order]
                        ) {
                            count
                            avg {
                                sampleInterval
                            }
                            sum {
                                visits
                            }
                            dimensions {
                                metric: requestHost
                            }
                        }
                        topBrowsers: rumPageloadEventsAdaptiveGroups(
                            filter: $filter, 
                            limit: 15, 
                            orderBy: [$order]
                        ) {
                            count
                            avg {
                                sampleInterval
                            }
                            sum {
                                visits
                            }
                            dimensions {
                                metric: userAgentBrowser
                            }
                        }
                        topOSs: rumPageloadEventsAdaptiveGroups(
                            filter: $filter, 
                            limit: 15, 
                            orderBy: [$order]
                        ) {
                            count
                            avg {
                                sampleInterval
                            }
                            sum {
                                visits
                            }
                            dimensions {
                                metric: userAgentOS
                            }
                        }
                        topDeviceTypes: rumPageloadEventsAdaptiveGroups(
                            filter: $filter, 
                            limit: 15, 
                            orderBy: [$order]
                        ) {
                            count
                            avg {
                                sampleInterval
                            }
                            sum {
                                visits
                            }
                            dimensions {
                                metric: deviceType
                            }
                        }
                        countries: rumPageloadEventsAdaptiveGroups(
                            filter: $filter, 
                            limit: 200, 
                            orderBy: [$order]
                        ) {
                            count
                            avg {
                                sampleInterval
                            }
                            sum {
                                visits
                            }
                            dimensions {
                                metric: countryName
                            }
                        }
                    }
                }
            }
        GQL;

        return $this->getCloudflareAnalytics($modelData, $query);
    }


    public function getRumPerfAnalyticsTopNs($modelData): array
    {
        $siteTag = Arr::get($modelData, 'siteTag');
        $accountTag = Arr::get($modelData, 'accountTag');
        $since = Arr::get($modelData, 'since') ?? Date::now()->subDay()->toIso8601String();
        $until = Arr::get($modelData, 'until') ?? Date::now()->toIso8601String();

        if (!$siteTag || !$accountTag) {
            return [];
        }

        $order = 'count_DESC';

        $filter = <<<FILTER
            {
                AND: [
                    { datetime_geq: "$since", datetime_leq: "$until" },
                    { OR: [{ siteTag: "$siteTag" }] },
                    { bot: 0 }
                ]
            }
        FILTER;

        $query = <<<GQL
        query Viewer {
            viewer {
                accounts(filter: { accountTag: "$accountTag" }) {
                total: rumPerformanceEventsAdaptiveGroups(filter: $filter, limit: 1) {
                    count
                    aggregation: quantiles {
                    pageLoadTime: pageLoadTimeP50
                    dnsTime: dnsTimeP50
                    connectionTime: connectionTimeP50
                    requestTime: requestTimeP50
                    responseTime: responseTimeP50
                    pageRenderTime: pageRenderTimeP50
                    loadEventTime: loadEventTimeP50
                    firstPaint: firstPaintP50
                    firstContentfulPaint: firstContentfulPaintP50
                    }
                }
                series: rumPerformanceEventsAdaptiveGroups(limit: 5000, filter: $filter) {
                    dimensions {
                    datetimeFifteenMinutes
                    }
                    count
                    aggregation: quantiles {
                    pageLoadTime: pageLoadTimeP50
                    dnsTime: dnsTimeP50
                    connectionTime: connectionTimeP50
                    requestTime: requestTimeP50
                    responseTime: responseTimeP50
                    pageRenderTime: pageRenderTimeP50
                    loadEventTime: loadEventTimeP50
                    firstPaint: firstPaintP50
                    firstContentfulPaint: firstContentfulPaintP50
                    }
                }
                countries: rumPerformanceEventsAdaptiveGroups(
                    filter: $filter
                    limit: 200
                    orderBy: [$order]
                ) {
                    count
                    avg {
                        sampleInterval
                    }
                    aggregation: quantiles {
                        pageLoadTime: pageLoadTimeP50
                    }
                    dimensions {
                        metric: countryName
                    }
                }
                topReferers: rumPerformanceEventsAdaptiveGroups(
                    filter: $filter
                    limit: 15
                    orderBy: [$order]
                ) {
                    count
                    avg {
                        sampleInterval
                    }
                    aggregation: quantiles {
                        pageLoadTime: pageLoadTimeP50
                    }
                    dimensions {
                        metric: refererHost
                    }
                }
                topPaths: rumPerformanceEventsAdaptiveGroups(
                    filter: $filter
                    limit: 15
                    orderBy: [$order]
                ) {
                    count
                    avg {
                        sampleInterval
                    }
                    aggregation: quantiles {
                        pageLoadTime: pageLoadTimeP50
                    }
                    dimensions {
                        metric: requestPath
                    }
                }
                topHosts: rumPerformanceEventsAdaptiveGroups(
                    filter: $filter
                    limit: 15
                    orderBy: [$order]
                ) {
                    count
                    avg {
                        sampleInterval
                    }
                    aggregation: quantiles {
                        pageLoadTime: pageLoadTimeP50
                    }
                    dimensions {
                        metric: requestHost
                    }
                }
                topBrowsers: rumPerformanceEventsAdaptiveGroups(
                    filter: $filter
                    limit: 15
                    orderBy: [$order]
                ) {
                    count
                    avg {
                        sampleInterval
                    }
                    aggregation: quantiles {
                        pageLoadTime: pageLoadTimeP50
                    }
                    dimensions {
                        metric: userAgentBrowser
                    }
                }
                topOSs: rumPerformanceEventsAdaptiveGroups(
                    filter: $filter
                    limit: 15
                    orderBy: [$order]
                ) {
                    count
                    avg {
                        sampleInterval
                    }
                    aggregation: quantiles {
                        pageLoadTime: pageLoadTimeP50
                    }
                    dimensions {
                        metric: userAgentOS
                    }
                }
                topDeviceTypes: rumPerformanceEventsAdaptiveGroups(
                    filter: $filter
                    limit: 15
                    orderBy: [$order]
                ) {
                    count
                    avg {
                    sampleInterval
                    }
                    aggregation: quantiles {
                        pageLoadTime: pageLoadTimeP50
                    }
                    dimensions {
                        metric: deviceType
                    }
                }
                }
            }
        }
        GQL;

        return $this->getCloudflareAnalytics($modelData, $query);

    }

    public function getRumSparkline($modelData): array
    {
        $siteTag = Arr::get($modelData, 'siteTag');
        $accountTag = Arr::get($modelData, 'accountTag');
        $since = Arr::get($modelData, 'since') ?? Date::now()->subDay()->toIso8601String();
        $until = Arr::get($modelData, 'until') ?? Date::now()->toIso8601String();

        if (!$siteTag || !$accountTag) {
            return [];
        }

        $dimension = 'datetimeFifteenMinutes';

        if ($this->isDate($since) && $this->isDate($until)) {
            $dimension = 'datetimeHour';
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

        $query = <<<GQL
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
                            ts: $dimension
                        }
                    }
                    pageviews: rumPageloadEventsAdaptiveGroups(limit: 5000, filter: $filter) {
                        count
                        avg {
                            sampleInterval
                        }
                        dimensions {
                            ts: $dimension
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
                            ts: $dimension
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

        return $this->getCloudflareAnalytics($modelData, $query);

    }

    public function getRumAnalytics($modelData): array
    {
        $siteTag = Arr::get($modelData, 'siteTag');
        $accountTag = Arr::get($modelData, 'accountTag');
        $since = Arr::get($modelData, 'since') ?? Date::now()->subDay()->toIso8601String();
        $until = Arr::get($modelData, 'until') ?? Date::now()->toIso8601String();

        if (!$siteTag || !$accountTag) {
            return [];
        }

        $dimension = 'datetimeFifteenMinutes';

        if ($this->isDate($since) && $this->isDate($until)) {
            $dimension = 'datetimeHour';
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

        $query = <<<GQL
        query Viewer {
            viewer {
                accounts(filter: { accountTag: "$accountTag" }) {
                series: rumPageloadEventsAdaptiveGroups(limit: 5000, filter: $filter) {
                    count
                    avg {
                        sampleInterval
                    }
                    sum {
                    visits
                    }
                    dimensions {
                        ts: $dimension
                    }
                }
                }
            }
        }
        GQL;

        return $this->getCloudflareAnalytics($modelData, $query);
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



    // public function getQueryWebVitals($modelData): ?string{
    //     $siteTag = Arr::get($modelData, 'siteTag');
    //     $accountTag = Arr::get($modelData, 'accountTag');
    //     $since = Arr::get($modelData, 'since') ?? Date::now()->subDay()->toIso8601String();
    //     $until = Arr::get($modelData, 'until') ?? Date::now()->toIso8601String();

    //     if (!$siteTag || !$accountTag) {
    //         return null;
    //     }

    //     return <<<GQL
    //     query Viewer {
    //         viewer {
    //             accounts(filter: {accountTag: $accountTag}) {
    //             total: rumWebVitalsEventsAdaptiveGroups(filter: $filter, limit: 1) {
    //                 count
    //                 aggregation: quantiles {
    //                 largestContentfulPaintP50
    //                 largestContentfulPaintP75
    //                 largestContentfulPaintP90
    //                 largestContentfulPaintP99
    //                 interactionToNextPaintP50
    //                 interactionToNextPaintP75
    //                 interactionToNextPaintP90
    //                 interactionToNextPaintP99
    //                 firstInputDelayP50
    //                 firstInputDelayP75
    //                 firstInputDelayP90
    //                 firstInputDelayP99
    //                 cumulativeLayoutShiftP50
    //                 cumulativeLayoutShiftP75
    //                 cumulativeLayoutShiftP90
    //                 cumulativeLayoutShiftP99
    //                 __typename
    //                 }
    //                 __typename
    //             }
    //             lcp: rumWebVitalsEventsAdaptiveGroups(filter: $lcpFilter, limit: 1) {
    //                 count
    //                 sum {
    //                 lcpTotal
    //                 lcpGood
    //                 lcpNeedsImprovement
    //                 lcpPoor
    //                 __typename
    //                 }
    //                 avg {
    //                 sampleInterval
    //                 __typename
    //                 }
    //                 __typename
    //             }
    //             lcpSeries: rumWebVitalsEventsAdaptiveGroups(filter: $lcpFilter, limit: 5000) {
    //                 dimensions {
    //                 datetimeFifteenMinutes
    //                 __typename
    //                 }
    //                 count
    //                 aggregation: quantiles {
    //                 largestContentfulPaintP50
    //                 largestContentfulPaintP75
    //                 largestContentfulPaintP90
    //                 largestContentfulPaintP99
    //                 __typename
    //                 }
    //                 __typename
    //             }
    //             lcpDebugView: rumWebVitalsEventsAdaptiveGroups(filter: $lcpDebugFilter, orderBy: [count_DESC], limit: 15) {
    //                 sum {
    //                 lcpTotal
    //                 lcpGood
    //                 lcpNeedsImprovement
    //                 lcpPoor
    //                 __typename
    //                 }
    //                 dimensions {
    //                 largestContentfulPaintElement
    //                 largestContentfulPaintObjectScheme
    //                 largestContentfulPaintObjectHost
    //                 largestContentfulPaintObjectPath
    //                 requestScheme
    //                 requestHost
    //                 requestPath
    //                 __typename
    //                 }
    //                 aggregation: quantiles {
    //                 largestContentfulPaintP50
    //                 largestContentfulPaintP75
    //                 largestContentfulPaintP90
    //                 largestContentfulPaintP99
    //                 __typename
    //                 }
    //                 __typename
    //             }
    //             inp: rumWebVitalsEventsAdaptiveGroups(filter: $inpFilter, limit: 1) {
    //                 count
    //                 sum {
    //                 inpTotal
    //                 inpGood
    //                 inpNeedsImprovement
    //                 inpPoor
    //                 __typename
    //                 }
    //                 avg {
    //                 sampleInterval
    //                 __typename
    //                 }
    //                 __typename
    //             }
    //             fid: rumWebVitalsEventsAdaptiveGroups(filter: $fidFilter, limit: 1) {
    //                 count
    //                 sum {
    //                 fidTotal
    //                 fidGood
    //                 fidNeedsImprovement
    //                 fidPoor
    //                 __typename
    //                 }
    //                 avg {
    //                 sampleInterval
    //                 __typename
    //                 }
    //                 __typename
    //             }
    //             fidDebugView: rumWebVitalsEventsAdaptiveGroups(filter: $fidDebugFilter, orderBy: [count_DESC], limit: 15) {
    //                 sum {
    //                 fidTotal
    //                 fidGood
    //                 fidNeedsImprovement
    //                 fidPoor
    //                 __typename
    //                 }
    //                 dimensions {
    //                 firstInputDelayElement
    //                 firstInputDelayName
    //                 requestScheme
    //                 requestHost
    //                 requestPath
    //                 __typename
    //                 }
    //                 aggregation: quantiles {
    //                 firstInputDelayP50
    //                 firstInputDelayP75
    //                 firstInputDelayP90
    //                 firstInputDelayP99
    //                 __typename
    //                 }
    //                 __typename
    //             }
    //             cls: rumWebVitalsEventsAdaptiveGroups(filter: $clsFilter, limit: 1) {
    //                 count
    //                 sum {
    //                 clsTotal
    //                 clsGood
    //                 clsNeedsImprovement
    //                 clsPoor
    //                 __typename
    //                 }
    //                 avg {
    //                 sampleInterval
    //                 __typename
    //                 }
    //                 __typename
    //             }
    //             clsDebugView: rumWebVitalsEventsAdaptiveGroups(filter: $clsDebugFilter, orderBy: [count_DESC], limit: 15) {
    //                 sum {
    //                 clsTotal
    //                 clsGood
    //                 clsNeedsImprovement
    //                 clsPoor
    //                 __typename
    //                 }
    //                 dimensions {
    //                 cumulativeLayoutShiftElement
    //                 cumulativeLayoutShiftPath
    //                 requestScheme
    //                 requestHost
    //                 requestPath
    //                 __typename
    //                 }
    //                 aggregation: quantiles {
    //                 cumulativeLayoutShiftP50
    //                 cumulativeLayoutShiftP75
    //                 cumulativeLayoutShiftP90
    //                 cumulativeLayoutShiftP99
    //                 __typename
    //                 }
    //                 __typename
    //             }
    //             __typename
    //             }
    //             __typename
    //         }
    //     }
    //     GQL;
    // }
}
