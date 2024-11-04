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
use Exception;
use Http;
use Illuminate\Support\Facades\Date;
use Lorisleiva\Actions\Concerns\AsAction;

class GetWebsiteCloudflareZoneID extends OrgAction
{
    use AsAction;
    use WithNoStrictRules;

    private Website $website;

    /**
     * @throws \Throwable
     */
    public function handle(Website $website, array $modelData): array
    {


        $groupSettigns = $website->group->settings;
        $apiToken = Arr::get($groupSettigns, 'cloudflare.apiToken');
        if (!$apiToken) {
            $apiToken = env('CLOUDFLARE_ANALYTICS_API_TOKEN'); // for now
            data_set($groupSettigns, 'cloudflare.apiToken', $apiToken);
            $website->group->update(['settings' => $groupSettigns]);
        }

        data_set($modelData, "apiToken", $apiToken);

        $dataWebsite = $website->data;
        $zoneTag = Arr::get($dataWebsite, "cloudflare.zoneTag");
        if (!$zoneTag) {
            $zoneTag = $this->getZoneTag($website, $modelData);
            if ($zoneTag == "error") {
                dd("error");
            }
            if (!Arr::get($dataWebsite, "cloudflare.zoneTag")) {
                data_set($dataWebsite, 'cloudflare.zoneTag', $zoneTag);
                $website->update(['data' => $dataWebsite]);
            }
        }

        data_set($modelData, "zoneTag", $zoneTag);

        return $this->getZoneAnalytics($modelData);
    }

    private function getZoneAnalytics(array $modelData): array
    {
        $zoneTag = Arr::get($modelData, "zoneTag");
        $apiToken = Arr::get($modelData, "apiToken");
        $since = Arr::get($modelData, "since");
        $until = Arr::get($modelData, "until");

        // get zone analytics
        $query = <<<GQL
            query Viewer {
                    viewer {
            zones(filter: { zoneTag: "$zoneTag" }) {
            totals: httpRequests1hGroups(
                limit: 10000
                filter: { datetime_geq: "$since", datetime_lt: "$until" }
            ) {
                uniq {
                uniques
                __typename
                }
                __typename
            }
            zones: httpRequests1hGroups(
                orderBy: [datetime_ASC]
                limit: 10000
                filter: { datetime_geq: "$since", datetime_lt: "$until" }
            ) {
                dimensions {
                timeslot: datetime
                __typename
                }
                uniq {
                uniques
                __typename
                }
                sum {
                browserMap {
                    pageViews
                    key: uaBrowserFamily
                    __typename
                }
                bytes
                cachedBytes
                cachedRequests
                contentTypeMap {
                    bytes
                    requests
                    key: edgeResponseContentTypeName
                    __typename
                }
                clientSSLMap {
                    requests
                    key: clientSSLProtocol
                    __typename
                }
                countryMap {
                    bytes
                    requests
                    threats
                    key: clientCountryName
                    __typename
                }
                encryptedBytes
                encryptedRequests
                ipClassMap {
                    requests
                    key: ipType
                    __typename
                }
                pageViews
                requests
                responseStatusMap {
                    requests
                    key: edgeResponseStatus
                    __typename
                }
                threats
                threatPathingMap {
                    requests
                    key: threatPathingName
                    __typename
                }
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


        $urlCLoudflareGraphql = "https://api.cloudflare.com/client/v4/graphql";
        $response = Http::withHeaders([
            'Authorization' => "Bearer $apiToken",
            'Content-Type' => 'application/json',
        ])->post($urlCLoudflareGraphql, [
            'query' => $query,
        ]);

        return $response->json();
    }

    private function getZoneTag(Website $website, array $modelData): string
    {
        $apiToken = Arr::get($modelData, "apiToken");
        $urlCLoudflareRest = "https://api.cloudflare.com/client/v4"; // -> api to get zone id, account id & site tag
        $resultZone = Http::withHeaders([
            'Authorization' => "Bearer $apiToken",
            'Content-Type' => 'application/json',
        ])->get($urlCLoudflareRest . "/zones", [
            'name' => $website->domain,
        ])->json();

        if (!empty($resultZone['errors'])) {
            print_r($resultZone);
            return "error";
        }
        if (!Arr::get($resultZone, 'result')) {
            print $website->domain . " zone tag not found";
            return "error";
        }

        return $resultZone['result'][0]['id'];
    }

    public function rules(): array
    {
        return [
            'since' => ['required'],
            'until' => ['required']
        ];
    }

    public function action(Website $website, array $modelData, bool $strict = true): array
    {
        $this->strict   = $strict;
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($website, $validatedData);
    }


    public string $commandSignature = "cloudflareWebsite:web-analytics {website?}";

    /**
     * @throws \Exception
     */
    public function asCommand($command): int
    {
        $day = [
            'since' => Date::now()->subDay()->toIso8601String(),
            'until' => Date::now()->toIso8601String()
        ]; // -> for command line only

        if ($command->argument("website")) {
            try {
                /** @var Website $website */
                $website = Website::where("slug", $command->argument("website"))->firstOrFail();
            } catch (Exception) {
                $command->error("Website not found");
                exit();
            }

            $this->action($website, $day);

            $command->line("Website ".$website->slug." fetched");

        } else {
            foreach (Website::orderBy('id')->get() as $website) {
                $command->line("Website ".$website->slug." fetched");
                $this->action($website, $day);
            }
        }


        return 0;
    }


}
