<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 06-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\Webpage\Hydrators;

use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class WebpageHydrateGoogleCloudData
{
    use AsAction;
    private Webpage $webpage;
    private Collection $siteList;
    private ?string $apiToken;
    private Collection $zoneAccountTag;

    public function __construct(Webpage $webpage)
    {
        $this->webpage = $webpage;
        $this->siteList = collect();
        $this->zoneAccountTag = collect();
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->webpage->id))->dontRelease()];
    }

    public function handle(Webpage $webpage): void
    {
        $settings = $webpage->group->settings;
        $this->apiToken = Arr::get($settings, 'gcp.oauthClientSecret');
        if (!$this->apiToken) {
            $this->apiToken = env('GOOGLE_OAUTH_CLIENT_SECRET');
            if (!$this->apiToken) {
                dd("secret is empty \n");
            }
            data_set($settings, "gcp.oauthClientSecret", $this->apiToken);
            $webpage->group->update(['settings' => $settings]);

        }
        $newWebpageData = $webpage->data;

        $webpage->update(['data' => $newWebpageData]);

        return;
    }

    public string $commandSignature = 'hydrate:webpage_data_google_cloud {website?} {webpage?}';

    public function asCommand($command)
    {
        $webSlug = $command->argument('webpage', 'Webpage slug');
        $websiteSlug = $command->argument('website', 'website slug');

        if ($webSlug) {
            $webpage = Webpage::where('slug', $webSlug)->first();
            if (!$webpage) {
                return;
            }
            $this->handle($webpage);
        } elseif ($websiteSlug) {
            $website = Website::where('slug', $websiteSlug)->first();
            if (!$website) {
                return;
            }
            $webpages = $website->webpages;
            $command->withProgressBar($webpages, function ($webpage) {
                $this->handle($webpage);
            });
        } else {
            $webpages = Webpage::orderBy('id')->get();
            $command->withProgressBar($webpages, function ($webpage) {
                $this->handle($webpage);
            });
        }
    }
}
