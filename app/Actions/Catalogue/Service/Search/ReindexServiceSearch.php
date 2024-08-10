<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Aug 2024 22:27:06 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Service\Search;

use App\Actions\HydrateModel;
use App\Models\Catalogue\Service;
use Illuminate\Support\Collection;

class ReindexServiceSearch extends HydrateModel
{
    public string $commandSignature = 'service:search {organisations?*} {--s|slugs=}';


    public function handle(Service $service): void
    {
        ServiceRecordSearch::run($service);
    }

    protected function getModel(string $slug): Service
    {
        return Service::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Service::withTrashed()->get();
    }
}
