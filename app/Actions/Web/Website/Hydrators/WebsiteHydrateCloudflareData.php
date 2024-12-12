<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 06-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\Website\Hydrators;

use App\Models\Web\Website;
use Exception;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class WebsiteHydrateCloudflareData
{
    use AsAction;
    private Website $website;
    private Collection $siteList;
    private ?string $apiToken;
    private Collection $zoneAccountTag;

    public function __construct(Website $website)
    {
        $this->website = $website;
        $this->siteList = collect();
        $this->zoneAccountTag = collect();
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->website->id))->dontRelease()];
    }

    public function handle(Website $website): void
    {
        $groupSettings = $website->group->settings;
        $this->apiToken = Arr::get($groupSettings, 'cloudflare.apiToken');
        if (!$this->apiToken) {
            $this->apiToken = env('CLOUDFLARE_ANALYTICS_API_TOKEN'); // from env cause group not stored api token yet
            if (!$this->apiToken) {
                dd('api token not found');
            }
            data_set($groupSettings, 'cloudflare.apiToken', $this->apiToken);
            $website->group->update(['settings' => $groupSettings]);

        }
        $newWebsiteData = $website->data;

        $this->zoneAccountTag = $this->getZoneAccountTag($website);
        if (!$this->zoneAccountTag->isEmpty()) {
            data_set($newWebsiteData, 'cloudflare.zoneTag', $this->zoneAccountTag->get('id'), true);
            data_set($newWebsiteData, 'cloudflare.accountTag', $this->zoneAccountTag->get('account')['id'], true);
        } else {
            return;
        }

        if ($this->siteList->isEmpty()) {
            $this->siteList = $this->getAnalyticsList($this->zoneAccountTag->get('account')['id']);
            if ($this->siteList->isEmpty()) {
                return;
            }
        }

        $this->siteList->each(function ($site) use ($website, $newWebsiteData) {
            if ($site['ruleset'] && $site['ruleset']['zone_name'] == $website->domain) {
                data_set($newWebsiteData, 'cloudflare.siteTag', $site['site_tag'], true);
                return;
            }
        });

        $website->update(['data' => $newWebsiteData]);

        return;
    }

    public string $commandSignature = 'hydrate:website-data-cloudflare {website?}';

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
            $websites = Website::all();
            $command->withProgressBar($websites, function ($website) {
                $this->handle($website);
            });
        }
    }

    private function getZoneAccountTag(Website $website, $try = 3): Collection
    {
        $urlCLoudflareRest = "https://api.cloudflare.com/client/v4";
        if ($try == 0) {
            return collect();
        }
        try {
            $resultZone = Http::timeout(10)->withHeaders([
            'Authorization' => "Bearer {$this->apiToken}",
            'Content-Type' => 'application/json',
            ])->get($urlCLoudflareRest . "/zones", [
            'name' => $website->domain,
            ])->json();
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Resolving timed out')) {
                return $this->getZoneAccountTag($website, --$try);
            }
            throw $e;
        }

        if (!empty($resultZone['errors'])) {
            return collect();
        }
        if (!Arr::get($resultZone, 'result')) {
            return collect();
        }
        if (empty($resultZone['result'])) {
            return collect();
        }
        return collect($resultZone['result'][0]);
    }

    private function getAnalyticsList(string $accountId, $try = 3): Collection
    {
        try {
            $urlCLoudflareResAnalytic = "https://api.cloudflare.com/client/v4/accounts/{$accountId}/rum/site_info/list";
            $resultAnalytic = Http::timeout(10)->withHeaders([
                'Authorization' => "Bearer {$this->apiToken}",
                'Content-Type' => 'application/json',
            ])->get($urlCLoudflareResAnalytic, [
                'per_page' => Website::count(),
            ])->json();
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Resolving timed out')) {
                return $this->getAnalyticsList($accountId, --$try);
            }
            throw $e;
        }
        if (!empty($resultAnalytic['errors'])) {
            return collect();
        }
        if (!Arr::get($resultAnalytic, 'result')) {
            return collect();
        }

        return collect($resultAnalytic['result']);
    }
}
