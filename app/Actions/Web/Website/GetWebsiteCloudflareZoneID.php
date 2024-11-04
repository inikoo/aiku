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
                return [];
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

        $urlCLoudflareGraphql = "https://api.cloudflare.com/client/v4/graphql";
        $response = Http::timeout(10)->withHeaders([
            'Authorization' => "Bearer $apiToken",
            'Content-Type' => 'application/json',
        ])->post($urlCLoudflareGraphql, [
            'query' => $this->getQuery($zoneTag, $modelData),
        ]);

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

    private function getQuery($zoneTag, $modelData): string
    {

        $since = Arr::get($modelData, 'since') ?? Date::now()->subDay()->toIso8601String();
        $until = Arr::get($modelData, 'until') ?? Date::now()->toIso8601String();

        if ($this->isIso8601($since) && $this->isIso8601($until)) {
            return  <<<GQL
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
        } elseif ($this->isDate($since) && $this->isDate($until)) {
            return <<<GQL
                query Viewer {
                    viewer {
                        zones(filter: { zoneTag: "$zoneTag" }) {
                        totals: httpRequests1dGroups(
                            limit: 10000
                            filter: { date_geq: "$since", date_lt: "$until" }
                        ) {
                            uniq {
                            uniques
                            __typename
                            }
                            __typename
                        }
                        zones: httpRequests1dGroups(
                            orderBy: [date_ASC]
                            limit: 10000
                            filter: { date_geq: "$since", date_lt: "$until" }
                        ) {
                            dimensions {
                            timeslot: date
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
        } else {
            return "error";
        }
    }

    private function getZoneTag(Website $website, array $modelData): string
    {
        $apiToken = Arr::get($modelData, "apiToken");
        $urlCLoudflareRest = "https://api.cloudflare.com/client/v4"; // -> api to get zone id, account id & site tag
        $resultZone = Http::timeout(10)->withHeaders([
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
            print $website->domain . " zone tag not found" . "\n";
            return "error";
        }

        return $resultZone['result'][0]['id'];
    }

    public function rules(): array
    {
        return [
            'since' => ['sometimes', 'required'],
            'until' => ['sometimes', 'required']
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

        if ($command->argument("website")) {
            try {
                /** @var Website $website */
                $website = Website::where("slug", $command->argument("website"))->firstOrFail();
            } catch (Exception) {
                $command->error("Website not found");
                exit();
            }

            $this->action($website, []);

            $command->line("Website ".$website->slug." fetched");

        } else {
            foreach (Website::orderBy('id')->get() as $website) {
                $command->line("Website ".$website->slug." fetched");
                $this->action($website, []);
            }
        }


        return 0;
    }


}
