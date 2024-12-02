<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 07 May 2024 23:00:40 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Production\Production;

use App\Actions\HydrateModel;
use App\Actions\Production\Production\Hydrators\ProductionHydrateManufactureTasks;
use App\Actions\Production\Production\Hydrators\ProductionHydrateRawMaterials;
use App\Models\Production\Production;
use Illuminate\Support\Collection;

class HydrateProduction extends HydrateModel
{
    public string $commandSignature = 'production:hydrate {organisations?*} {--s|slugs=}';

    public function handle(Production $production): void
    {
        ProductionHydrateRawMaterials::run($production);
        ProductionHydrateManufactureTasks::run($production);
    }

    protected function getModel(string $slug): Production
    {
        return Production::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Production::all();
    }
}
