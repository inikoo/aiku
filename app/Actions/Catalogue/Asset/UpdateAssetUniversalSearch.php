<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 18:29:16 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Asset;

use App\Actions\HydrateModel;
use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateUniversalSearch;
use App\Models\Catalogue\Asset;
use Illuminate\Support\Collection;

class UpdateAssetUniversalSearch extends HydrateModel
{
    public string $commandSignature = 'asset:search {organisations?*} {--s|slugs=}';


    public function handle(Asset $product): void
    {
        AssetHydrateUniversalSearch::run($product);
    }


    protected function getModel(string $slug): Asset
    {
        return Asset::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Asset::get();
    }
}
