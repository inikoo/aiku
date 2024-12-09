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
use Illuminate\Support\Facades\Http;
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

        $partialShowTopNs = Arr::get($modelData, 'partialShowTopNs');
        switch ($partialShowTopNs) {
            case 'performance':
                return $this->getRumPerfAnalyticsTopNs($modelData);
                // case 'webVitals':
                //     break;
            case 'pageViews':
            case 'visits':
                return $this->getRumAnalyticsTopNs($modelData);
        }

        $showTopNs = Arr::get($modelData, 'showTopNs') ?? 'visits';
        $rumAnalyticsTopNsPromise = async(fn () => ['data' => [], 'errors' => null]);
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
                    break;
            }
        }

        $rumSparklinePromise = async(fn () => $this->getRumSparkline($modelData));
        $rumAnalyticsPromise = async(fn () => $this->getRumAnalytics($modelData));
        $zonePromise = async(fn () => $this->getZone($modelData));

        [$rumAnalyticsTopNs, $rumSparkline, $zone, $rumAnalytics] = await([$rumAnalyticsTopNsPromise,$rumSparklinePromise, $zonePromise, $rumAnalyticsPromise]);

        $data = [
            'rumAnalyticsTopNs' => $rumAnalyticsTopNs,
            'rumSparkline' => $rumSparkline,
            'rumAnalytics' => $rumAnalytics,
            'zone' => $zone,
        ];

        dd($data);
        // cache()->put($cacheKey, $data, $cacheTTL);

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

    public function rules(): array
    {
        return [
            'since' => ['sometimes'],
            'until' => ['sometimes'],
            'partialShowTopNs' => ['sometimes', 'string', 'in:visits,pageViews,performance,webVitals'],
            'showTopNs' => ['sometimes', 'string', 'in:visits,pageViews,performance,webVitals'],
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
