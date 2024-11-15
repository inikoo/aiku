<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 15-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Catalogue\Collection\Search;

use App\Actions\HydrateModel;
use App\Models\Catalogue\Collection as CatalogueCollection;
use Illuminate\Support\Collection;

class ReindexCollectionSearch extends HydrateModel
{
    public string $commandSignature = 'collection:search {organisations?*} {--s|slugs=}';


    public function handle(CatalogueCollection $collection): void
    {
        CollectionRecordSearch::run($collection);
    }

    protected function getModel(string $slug): CatalogueCollection
    {
        return CatalogueCollection::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return CatalogueCollection::withTrashed()->get();
    }
}
