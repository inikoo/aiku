<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:43:03 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location\Search;

use App\Actions\HydrateModel;
use App\Models\Inventory\Location;
use Illuminate\Support\Collection;

class ReindexLocationSearch extends HydrateModel
{
    public string $commandSignature = 'location:search {organisations?*} {--s|slugs=}';


    public function handle(Location $location): void
    {
        LocationRecordSearch::run($location);
    }


    protected function getModel(string $slug): Location
    {
        return Location::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Location::withTrashed()->get();
    }
}
