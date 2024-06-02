<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 09:00:59 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Service;

use App\Actions\Catalogue\Service\Hydrators\ServiceHydrateHistoricAssets;
use App\Actions\HydrateModel;
use App\Models\Catalogue\Service;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Collection;

class HydrateService extends HydrateModel
{
    public string $commandSignature = 'service:hydrate {organisations?*} {--s|slugs=} ';


    public function handle(Service $service): void
    {

        ServiceHydrateHistoricAssets::run($service);

    }


    protected function getModel(string $slug): Shop
    {
        return Shop::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Shop::withTrashed()->get();
    }
}
