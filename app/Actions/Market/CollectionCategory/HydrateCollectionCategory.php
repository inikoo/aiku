<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 09 Feb 2022 15:04:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Market\CollectionCategory;

use App\Actions\HydrateModel;
use App\Actions\Market\CollectionCategory\Hydrators\CollectionCategoryHydrateCollections;
use App\Models\Market\CollectionCategory;
use Illuminate\Support\Collection;

class HydrateCollectionCategory extends HydrateModel
{
    public string $commandSignature = 'collection-category:hydrate {organisations?*} {--s|slugs=} ';


    public function handle(CollectionCategory $collectionCategory): void
    {
        CollectionCategoryHydrateCollections::run($collectionCategory);
    }

    protected function getModel(string $slug): CollectionCategory
    {
        return CollectionCategory::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return CollectionCategory::withTrashed()->get();
    }
}
