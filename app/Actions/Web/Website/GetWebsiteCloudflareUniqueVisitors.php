<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 11-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\Website;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Web\Website;
use Arr;
use Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Date;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class GetWebsiteCloudflareUniqueVisitors extends OrgAction
{
    use AsAction;
    use WithNoStrictRules;

    private Website $website;
    /**
     * @throws \Throwable
     */
    public function handle(Website $website): array
    {
        $groupSettings = $website->group->settings;
        $apiToken = Arr::get($groupSettings, 'cloudflare.apiToken');
        if (!$apiToken) {
            $apiToken = env('CLOUDFLARE_ANALYTICS_API_TOKEN'); // from env cause group not stored api token yet
            if (!$apiToken) {
                dd('api token not found');
            }
            data_set($groupSettings, 'cloudflare.apiToken', $apiToken);
            $website->group->update(['settings' => $groupSettings]);
        }

        data_set($modelData, "apiToken", $apiToken);

        $dataWebsite = $website->data;
        $zoneTag = Arr::get($dataWebsite, "cloudflare.zoneTag");
        if (!$zoneTag) {
            $zoneTag = $this->getZoneTag($website, $modelData);
            if ($zoneTag == "error") {
                return [];
            }
            if (!Arr::get($dataWebsite, "cloudflare.zoneTag")) {
                data_set($dataWebsite, 'cloudflare.zoneTag', $zoneTag);
                $website->update(['data' => $dataWebsite]);
            }
        }

        if ($this->saveSecret) {
            return [];
        }

        data_set($modelData, "zoneTag", $zoneTag);

        return $this->getUniqueVisitor($modelData);
    }

    public function asController(Website $website, ActionRequest $request): array
    {
        $this->initialisationFromShop($website->shop, []);

        return $this->handle($website);
    }

    public function jsonResponse(array $data): array
    {
        return $data;
    }

    private function getZoneTag(Website $website, array $modelData, $try = 3): string
    {
        $apiToken = Arr::get($modelData, "apiToken");
        $urlCLoudflareRest = "https://api.cloudflare.com/client/v4"; // -> api to get zone id, account id & site tag
        if ($try == 0) {
            return "error";
        }
        try {
            $resultZone = Http::timeout(10)->withHeaders([
                'Authorization' => "Bearer $apiToken",
                'Content-Type' => 'application/json',
            ])->get($urlCLoudflareRest . "/zones", [
                'name' => $website->domain,
            ])->json();
        } catch (ConnectionException $e) {
            return $this->getZoneTag($website, $modelData, $try--);
        }

        if (!empty($resultZone['errors'])) {
            print_r($resultZone);
            return "error";
        }
        if (!Arr::get($resultZone, 'result')) {
            print $website->domain . " zone tag not found" . "\n";
            return "error";
        }

        return $resultZone['result'][0]['id'];
    }

    private function getUniqueVisitor(array $modelData, $try = 3): array
    {
        $zoneTag = Arr::get($modelData, "zoneTag");
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
                'query' => $this->getQuery($zoneTag, $modelData),
            ]);
        } catch (ConnectionException $e) {
            return $this->getUniqueVisitor($modelData, $try--);
        }


        return $response->json();
    }

    private function getQuery($zoneTag): string
    {
        $currDate = Date::now();
        $since = $currDate->copy()->subDays(30)->toDateString();
        $until = $currDate->toDateString();

        return <<<GQL
        query Viewer {
            viewer {
                zones(filter: { zoneTag: "$zoneTag" }) {
                byDay: httpRequests1dGroups(
                    orderBy: [date_ASC]
                    limit: 1000
                    filter: { date_geq: "$since", date_lt: "$until" }
                ) {
                    dimensions {
                    ts: date
                    __typename
                    }
                    uniq {
                    uniques
                    __typename
                    }
                    __typename
                }
                totals: httpRequests1dGroups(
                    limit: 1000
                    filter: { date_geq: "$since", date_lt: "$until" }
                ) {
                    uniq {
                    uniques
                    __typename
                    }
                    __typename
                }
                __typename
                }
                __typename
            }
        }
    GQL;
    }

    // public string $commandSignature = "cloudflareWebsite:unique-visitors {limit}";

    // /**
    //  * @throws \Exception
    //  */
    // public function asCommand($command): int
    // {

    //     $limit = $command->argument("limit");
    //     $websites = Website::orderBy('id')->take($limit)->get();
    //     $res = $this->handle($websites);
    //     dd($res);


    //     return 0;
    // }


}
