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
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Date;
use Lorisleiva\Actions\Concerns\AsAction;

class GetWebsiteCloudflareZoneID extends OrgAction
{
    use AsAction;
    use WithNoStrictRules;

    private Website $website;
    private bool $saveSecret = false;
    /**
     * @throws \Throwable
     */
    public function handle(Website $website, array $modelData): array
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

        return $this->getZoneAnalytics($modelData);
    }

    private function getZoneAnalytics(array $modelData, $try = 3): array
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
            return $this->getZoneAnalytics($modelData, $try--);
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

    private function getQuery($zoneTag, $modelData): string
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
                            __typename
                        }
                        __typename
                    }
                    zones: $groupsFunction(
                        orderBy: [$orderBy]
                        limit: 10000
                        filter: { {$timeField}_geq: "$since", {$timeField}_lt: "$until" }
                    ) {
                        dimensions {
                            timeslot: $timeField
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


    public string $commandSignature = "cloudflareWebsite:web-analytics {website?} {--saveSecret}";

    /**
     * @throws \Exception
     */
    public function asCommand($command): int
    {
        $this->saveSecret = $command->option("saveSecret");
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
