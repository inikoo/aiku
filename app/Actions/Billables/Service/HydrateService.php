<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 15:22:15 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Billables\Service;

use App\Actions\Billables\Service\Hydrators\ServiceHydrateHistoricAssets;
use App\Actions\HydrateModel;
use App\Models\Billables\Service;
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
