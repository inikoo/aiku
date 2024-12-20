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
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ReindexCollectionSearch extends HydrateModel
{
    public string $commandSignature = 'search:collections {organisations?*} {--s|slugs=}';


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

    protected function loopAll(Command $command): void
    {
        $command->info("Reindex Collections");
        $count = CatalogueCollection::withTrashed()->count();

        $bar = $command->getOutput()->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();

        CatalogueCollection::withTrashed()->chunk(1000, function (Collection $models) use ($bar) {
            foreach ($models as $model) {
                $this->handle($model);
                $bar->advance();
            }
        });

        $bar->finish();
        $command->info("");
    }
}
