<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 01-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\Webpage;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Web\Website;
use App\Models\Web\Webpage;
use Exception;
use Http;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAnalyticCloudflare extends OrgAction
{
    use AsAction;
    use WithNoStrictRules;

    private Website $website;

    private Webpage|Website $parent;

    /**
     * @throws \Throwable
     */
    public function handle(Webpage $webpage)
    {
        $websiteDomain = $webpage->website->domain;

        $webpagefull = $webpage->getFullUrl();

        $urlCLoudflare = "https://api.cloudflare.com/client/v4/graphql";

        $zoneTag = "5800b20d36b53d4452f31fab003c46b7";
        $since = "2024-10-31T05:00:00Z";
        $until = "2024-11-01T05:00:00Z";
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

        $apiToken = env('CLOUDFLARE_ANALYTICS_API_TOKEN');

        $response = Http::withHeaders([
            'Authorization' => "Bearer $apiToken",
            'Content-Type' => 'application/json',
        ])->post('https://api.cloudflare.com/client/v4/graphql', [
            'query' => $query,
        ]);

        dd($response->json());


    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("web.{$this->shop->id}.edit");
    }


    public string $commandSignature = "cloudflare:web-analytics {webpage?}";

    /**
     * @throws \Exception
     */
    public function asCommand($command): int
    {
        if ($command->argument("webpage")) {
            try {
                /** @var Webpage $webpage */
                $webpage = Webpage::where("slug", $command->argument("webpage"))->firstOrFail();
            } catch (Exception) {
                $command->error("Webpage not found");
                exit();
            }
            $this->handle($webpage);
            $command->line("Webpage ".$webpage->slug." web blocks fetched");

        }

        return 0;
    }


}
