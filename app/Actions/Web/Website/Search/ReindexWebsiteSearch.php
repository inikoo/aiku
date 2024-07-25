<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 12:56:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\Search;

use App\Actions\HydrateModel;
use App\Models\Web\Website;
use Illuminate\Support\Collection;

class ReindexWebsiteSearch extends HydrateModel
{
    public string $commandSignature = 'website:search {organisations?*} {--s|slugs=} ';


    public function handle(Website $website): void
    {
        WebsiteRecordSearch::run($website);
    }

    protected function getModel(string $slug): Website
    {
        return Website::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Website::withTrashed()->get();
    }
}
