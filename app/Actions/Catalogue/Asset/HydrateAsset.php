<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Feb 2023 22:01:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Asset;

use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateHistoricAssets;
use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateSales;
use App\Actions\HydrateModel;
use App\Models\Catalogue\Asset;
use Illuminate\Support\Collection;

class HydrateAsset extends HydrateModel
{
    public string $commandSignature = 'asset:hydrate {organisations?*} {--slug=} ';


    public function handle(Asset $asset): void
    {
        AssetHydrateHistoricAssets::run($asset);
        AssetHydrateSales::run($asset);

    }


    protected function getModel(string $slug): Asset
    {
        return Asset::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Asset::withTrashed()->get();
    }
}
