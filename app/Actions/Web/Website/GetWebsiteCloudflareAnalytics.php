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
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

use function Amp\async;
use function Amp\Future\await;

class GetWebsiteCloudflareAnalytics extends OrgAction
{
    use AsAction;
    use WithNoStrictRules;
    use WithCloudflareQueryGraphql;

    private Website $website;
    /**
     * @throws \Throwable
     */
    public function handle(Website $website, array $modelData): array
    {

        // $cacheKey = "cloudflare_analytics_{$website->id}_" . md5(json_encode($modelData));
        // $cacheTTL = now()->addMinutes(30); // Cache for 30 minutes

        // $cachedData = cache()->get($cacheKey);

        // if ($cachedData) {
        //     return $cachedData;
        // }

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


        $partialShowTopNsData = Arr::get($modelData, 'partialShowTopNs');
        $partialFilterTimeseriesData = Arr::get($modelData, 'partialFilterTimeseries');
        $partialFilterPerfAnalyticsData = Arr::get($modelData, 'partialFilterPerfAnalytics');
        $partialTimeseriesData = Arr::get($modelData, 'partialTimeseriesData');


        if ($partialShowTopNsData) {
            data_set($modelData, "showTopNs", $partialShowTopNsData);
            data_forget($modelData, 'partialShowTopNsData');

            switch ($partialShowTopNsData) {
                case 'performance':
                    data_set($modelData, 'filter', $partialFilterPerfAnalyticsData);
                    return $this->getRumPerfAnalyticsTopNs($modelData);
                case 'pageViews':
                case 'visits':
                    data_set($modelData, 'filterData', $partialTimeseriesData);
                    data_set($modelData, 'filter', $partialFilterTimeseriesData);
                    return $this->getRumAnalyticsTimeseries($modelData);
                default:
                    return $this->partialShowTopNs($modelData);
            }
        }


        $showTopNs = Arr::get($modelData, 'showTopNs') ?? 'visits';
        $rumAnalyticsTopNsPromise = async(fn () => ['data' => [], 'errors' => null]);
        $rumAnalyticsTimeseriesPromise = async(fn () => ['data' => [], 'errors' => null]);
        if ($showTopNs) {
            switch ($showTopNs) {
                case 'performance':
                    $rumAnalyticsTopNsPromise = async(fn () => $this->getRumPerfAnalyticsTopNs($modelData));
                    break;
                    // case 'webVitals':
                    //     $rumAnalyticsTopNsPromise = async(fn () => $this->getRumWebVitalsTopNs($modelData));
                    //     break;
                case 'pageViews':
                case 'visits':
                    $rumAnalyticsTopNsPromise = async(fn () => $this->getRumAnalyticsTopNs($modelData));
                    $rumAnalyticsTimeseriesPromise = async(fn () => $this->getRumAnalyticsTimeseries($modelData));
                    break;
            }
        }

        $rumSparklinePromise = async(fn () => $this->getRumSparkline($modelData));
        $zonePromise = async(fn () => $this->getZone($modelData));

        [$rumAnalyticsTopNs, $rumSparkline, $rumAnalyticsTimeseriesPromise, $zone] = await([$rumAnalyticsTopNsPromise,$rumSparklinePromise, $rumAnalyticsTimeseriesPromise, $zonePromise]);

        $data = [
            'rumAnalyticsTopNs' => $rumAnalyticsTopNs,
            'rumSparkline' => $rumSparkline,
            'rumAnalyticsTimeseries' => $rumAnalyticsTimeseriesPromise, // if performance, return empty array. count field -> for pageViews and sum.visits field -> for visits
            'zone' => $zone,
        ];

        dd($data);
        // cache()->put($cacheKey, $data, $cacheTTL);

        return $data;
    }

    private function partialShowTopNs($modelData): array
    {
        switch (Arr::get($modelData, 'partialShowTopNs')) {
            case 'performance':
                return $this->getRumPerfAnalyticsTopNs($modelData);
                // case 'webVitals':
                //     break;
            case 'pageViews':
            case 'visits':
                return $this->getRumAnalyticsTopNs($modelData);
            default:
                return [];
        }
    }

    // private function partialFilterTimeseries($modelData): array {
    //     switch (Arr::get($modelData, 'partialFilterTimeseries')) {
    //         case 'all':
    //         case 'referer':
    //         case 'host':
    //         case 'country':
    //         case 'path':
    //         case 'browser':
    //         case 'os':
    //         case 'deviceType':
    //         default:
    //             return [];
    //     }
    // }

    // private function partialFilterPerfAnalytics($modelData): array {
    //     switch (Arr::get($modelData, 'partialFilterPerfAnalytics')) {
    //         case 'p50':
    //         case 'p75':
    //         case 'p90':
    //         case 'p99':
    //         case 'avg':
    //             // return $this->getRumPerfAnalyticsTopNs($modelData);
    //         default:
    //             return [];
    //     }
    // }

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

    public function rules(): array
    {
        return [
            'since' => ['sometimes'],
            'until' => ['sometimes'],
            'showTopNs' => ['sometimes', 'string', 'in:visits,pageViews,performance,webVitals'],
            'partialShowTopNs' => ['sometimes', 'string', 'in:visits,pageViews,performance,webVitals'],
            'partialFilterTimeseries' => ['sometimes', 'string', 'in:all,referer,host,country,path,browser,os,deviceType'],
            'partialTimeseriesData' => ['sometimes', 'string'],
            'partialFilterPerfAnalytics' => ['sometimes', 'string', 'in:p50,p75,p90,p99,avg'],
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
