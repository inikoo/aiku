<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 11-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Web\Website\Hydrators;

use App\Models\Web\Website;
use Exception;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Google\Client;
use Google\Service\Webmasters;
use Lorisleiva\Actions\Concerns\AsAction;
use GuzzleHttp\Exception\ConnectException;

class WebsiteHydrateGoogleCloudSearch
{
    use AsAction;
    private Website $website;
    private ?string $apiToken;

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->website->id))->dontRelease()];
    }

    public function handle(Website $website): void
    {
        $groupSettings = $website->group->settings;
        $this->apiToken = Arr::get($groupSettings, 'gcp.oauthClientSecret');
        if (!$this->apiToken) {
            $this->apiToken = env('GOOGLE_OAUTH_CLIENT_SECRET');
            if (!$this->apiToken) {
                dd("secret is empty \n");
            }
            data_set($groupSettings, 'gcp.oauthClientSecret', $this->apiToken);
            $website->group->update(['settings' => $groupSettings]);

        }

        $client = new Client();
        $gcpOauthClientSecretDecoded = base64_decode($this->apiToken);
        $client->setAuthConfig(json_decode($gcpOauthClientSecretDecoded, true));
        $client->addScope(Webmasters::WEBMASTERS_READONLY);
        $service = new Webmasters($client);

        $this->saveSiteUrl($website, $service);

        return;
    }

    private function saveSiteUrl(Website $website, $service, $retry = 3)
    {
        if ($retry == 0) {
            return '';
        }
        $websiteData = $website->data;
        $siteUrl = Arr::get($websiteData, 'gcp.siteUrl');
        if (!$siteUrl) {
            try {
                $siteEntry = $service->sites->listSites()->getSiteEntry();
                $listSite = Arr::pluck($siteEntry, "siteUrl");
                $siteUrl = Arr::where($listSite, function (string $value) use ($website) {
                    return str_contains($value, $website->website->domain);
                });
                if (empty($siteUrl)) {
                    return;
                }
                $siteUrl = Arr::first($siteUrl);
                data_set($websiteData, 'gcp.siteUrl', $siteUrl);
                $website->update(['data' => $websiteData]);
            } catch (ConnectException) {
                return $this->saveSiteUrl($website, $service, $retry - 1);
            } catch (Exception $e) {
                debug($e);
            }
        }
    }

    public string $commandSignature = 'hydrate:website_data_google_cloud {website?}';

    public function asCommand($command)
    {
        $webSlug = $command->argument('website', 'Website slug');

        if ($webSlug) {
            $website = Website::where('slug', $webSlug)->first();
            if (!$website) {
                return;
            }
            $this->handle($website);
        } else {
            $websites = Website::orderBy('id')->get();
            $command->withProgressBar($websites, function ($website) {
                $this->handle($website);
            });
        }
    }

}
