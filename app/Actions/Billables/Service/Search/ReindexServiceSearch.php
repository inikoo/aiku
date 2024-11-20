<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 15:22:15 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Billables\Service\Search;

use App\Actions\HydrateModel;
use App\Models\Billables\Service;
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
