<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 07 Jun 2023 01:01:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\HydrateModel;

use App\Actions\Web\Website\Hydrators\WebsiteHydrateWebpages;
use App\Models\Web\Website;
use Illuminate\Support\Collection;

class HydrateWebsite extends HydrateModel
{
    public string $commandSignature = 'websites:hydrate {organisations?*} {--s|slugs=} ';


    public function handle(Website $website): void
    {
        WebsiteHydrateWebpages::run($website);
    }

    protected function getModel(string $slug): Website
    {
        return Website::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Website::withTrashed()->get();
    }
}
