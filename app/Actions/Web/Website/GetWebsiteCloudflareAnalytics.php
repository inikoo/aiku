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

        $user = auth()->user();


        $cacheKey = "ui_state-user:{$user->id};website:{$website->id};filter-analytics:" . md5(json_encode($modelData));

        $cachedData = cache()->get($cacheKey);

        if ($cachedData) {
            return $cachedData;
        }

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

        $partialShowTopNs = Arr::get($modelData, 'partialShowTopNs');
        if ($partialShowTopNs) {
            return $this->partialHandle($partialShowTopNs, $modelData);
        }


        $showTopNs = Arr::get($modelData, 'showTopNs') ?? 'visits';
        $rumAnalyticsTopNsPromise = async(fn () => []);
        $rumAnalyticsTimeseriesPromise = async(fn () => []);
        $rumWebVitalsPromise = async(fn () => []);
        if ($showTopNs) {
            switch ($showTopNs) {
                case 'performance':
                    $rumAnalyticsTopNsPromise = async(fn () => $this->getRumPerfAnalyticsTopNs($modelData));
                    break;
                case 'webVitals':
                    $rumAnalyticsTopNsPromise = async(fn () => $this->getRumWebVitalsTop($modelData));
                    $section = ['lcp', 'fid', 'inp' ,'cls'];
                    $rumWebVitalsPromise = [];
                    foreach ($section as $key) {
                        data_set($modelData, 'section', $key);
                        $rumWebVitalsPromise[$key] = async(fn () => $this->getRumWebVitals($modelData));
                    }
                    break;
                case 'pageViews':
                case 'visits':
                    $rumAnalyticsTopNsPromise = async(fn () => $this->getRumAnalyticsTopNs($modelData));
                    $rumAnalyticsTimeseriesPromise = async(fn () => $this->getRumAnalyticsTimeseries($modelData));
                    break;
            }
        }

        $rumSparklinePromise = async(fn () => $this->getRumSparkline($modelData));
        $zonePromise = async(fn () => $this->getZone($modelData));

        [$rumAnalyticsTopNs, $rumSparkline, $rumAnalyticsTimeseries, $zone] = await([
            $rumAnalyticsTopNsPromise,
            $rumSparklinePromise,
            $rumAnalyticsTimeseriesPromise,
            $zonePromise
        ]);
        $rumWebVitals = [];
        foreach ($rumWebVitalsPromise as $key => $promise) {
            [$webVital] = await([$promise]);
            $rumWebVitals[$key] = $webVital;
        }

        $data = array_filter([
            'rumAnalyticsTopNs' => $rumAnalyticsTopNs,
            'rumSparkline' => $rumSparkline,
            'rumAnalyticsTimeseries' => $rumAnalyticsTimeseries,
            'rumWebVitals' => $rumWebVitals,
            'zone' => $zone,
        ]);

        cache()->put($cacheKey, $data, now()->addMinutes(5));

        return $data;
    }

    private function partialHandle($partialShowTopNs, $modelData): array
    {
        $partialFilterTimeseries = Arr::get($modelData, 'partialFilterTimeseries');
        $partialFilterPerfAnalytics = Arr::get($modelData, 'partialFilterPerfAnalytics');
        $partialTimeseriesData = Arr::get($modelData, 'partialTimeseriesData');
        data_set($modelData, "showTopNs", $partialShowTopNs);
        data_forget($modelData, 'partialShowTopNs');
        switch ($partialShowTopNs) {
            case 'performance':
                data_set($modelData, 'filter', $partialFilterPerfAnalytics);
                return $this->getRumPerfAnalyticsTopNs($modelData);
                // case 'webVitals':
                //     $rumAnalyticsTopNsPromise = async(fn () => $this->getRumWebVitalsTop($modelData));
                //     break;
            case 'pageViews':
            case 'visits':
                $rumAnalyticsTopNsPromise = async(fn () => $this->getRumAnalyticsTopNs($modelData));
                data_set($modelData, 'filterData', $partialTimeseriesData);
                data_set($modelData, 'filter', $partialFilterTimeseries);
                $rumAnalyticsTimeseriesPromise = async(fn () => $this->getRumAnalyticsTimeseries($modelData));
                [$rumAnalyticsTopNs, $rumAnalyticsTimeseries] = await([$rumAnalyticsTopNsPromise, $rumAnalyticsTimeseriesPromise]);
                return [
                    'rumAnalyticsTopNs' => $rumAnalyticsTopNs,
                    'rumAnalyticsTimeseries' => $rumAnalyticsTimeseries,
                ];
            default:
                return [];
        }
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

    public function rules(): array
    {
        return [
            'since' => ['sometimes'],
            'until' => ['sometimes'],
            'showTopNs' => ['sometimes', 'string', 'in:visits,pageViews,performance,webVitals'],
            'partialShowTopNs' => ['sometimes', 'string', 'in:visits,pageViews,performance,webVitals'],
            'partialFilterTimeseries' => [
                'sometimes',
                'string',
                'required_with:partialShowTopNs',
                'in:all,referer,host,country,path,browser,os,deviceType'
            ],
            'partialTimeseriesData' => [
                'sometimes',
                'string',
                'required_with:partialFilterTimeseries'
            ],
            'partialFilterPerfAnalytics' => [
                'sometimes',
                'string',
                'required_with:partialShowTopNs',
                'in:p50,p75,p90,p99,avg'
            ],
            'partialWebVitals' => [
                'sometimes',
                'string',
                'required_with:partialShowTopNs',
                'in:lcp,inp,fid,cls'
            ],
            'partialWebVitalsData' => [
                'sometimes',
                'string',
                'in:url,browser,os,country,element'
            ],
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
        $this->website  = $website;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($website, $validatedData);
    }

}
