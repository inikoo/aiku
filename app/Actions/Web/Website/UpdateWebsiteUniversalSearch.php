<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 07 Jun 2023 01:01:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\HydrateModel;

use App\Actions\Web\Website\Hydrators\WebsiteHydrateUniversalSearch;
use App\Models\Web\Website;
use Illuminate\Support\Collection;

class UpdateWebsiteUniversalSearch extends HydrateModel
{
    public string $commandSignature = 'website:search {organisations?*} {--s|slugs=} ';


    public function handle(Website $website): void
    {
        WebsiteHydrateUniversalSearch::run($website);
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
