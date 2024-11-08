<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 07-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\Website;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Web\Website;
use Exception;
use Google\Client;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;
use Illuminate\Support\Facades\Date;
use Lorisleiva\Actions\Concerns\AsAction;

class GetWebsiteGoogleCloud extends OrgAction
{
    use AsAction;
    use WithNoStrictRules;

    private Website $website;

    /**
     * @throws \Throwable
     */
    public function handle(Website $website, array $modelData): void
    {
        $clientID = env("GOOGLE_OAUTH_CLIENT_SECRET");

        $jsonClientID = base64_decode($clientID);
        $client = new Client();
        $client->setAuthConfig(json_decode($jsonClientID, true));
        $client->addScope('https://www.googleapis.com/auth/webmasters');
        $client->addScope('https://www.googleapis.com/auth/webmasters.readonly');
        $service = new SearchConsole($client);
        $siteUrl = "https://www." . $website->domain;
        // $http = $client->authorize();
        // $endPint = "https://www.googleapis.com/webmasters/v3/sites";
        $query = new SearchAnalyticsQueryRequest();
        $query->startDate = Date::now()->subWeek()->toDateString();
        $query->endDate = Date::now()->toDateString();

        $res = $service->searchanalytics->query($siteUrl, $query);
        foreach ($res->getRows() as $row) {
            // dd($row);
            // $query = $row->keys[0];        // Search query
            $clicks = $row->clicks;        // Number of clicks
            $impressions = $row->impressions; // Number of impressions
            $ctr = $row->ctr;              // Click-through rate
            $position = $row->position;    // Average position

            echo "Clicks: $clicks, Impressions: $impressions, CTR: $ctr, Position: $position\n";
        }
        // dd($res);
        // try {
        //     // Execute the request
        //     $response = $service->searchanalytics->query($siteUrl, $req);

        //     // Output the results
        //     foreach ($response->getRows() as $row) {
        //         $query = $row->keys[0];         // The search query
        //         $clicks = $row->clicks;          // Number of clicks
        //         $impressions = $row->impressions; // Number of impressions
        //         $ctr = $row->ctr;                // Click-through rate
        //         $position = $row->position;      // Average position in search results

        //         echo "Query: $query, Clicks: $clicks, Impressions: $impressions, CTR: $ctr, Position: $position\n";
        //     }
        // } catch (Exception $e) {
        //     echo 'An error occurred: ' . $e->getMessage();
        // }
    }

    public string $commandSignature = "gcp:search-result {website?}";

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

            $this->handle($website, []);

            $command->line("Website ".$website->slug." fetched");

        } else {
            foreach (Website::orderBy('id')->get() as $website) {
                $command->line("Website ".$website->slug." fetched");
                $this->handle($website, []);
            }
        }


        return 0;
    }


}
