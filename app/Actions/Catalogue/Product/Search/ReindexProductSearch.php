<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Aug 2024 22:33:24 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product\Search;

use App\Actions\HydrateModel;
use App\Models\Catalogue\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ReindexProductSearch extends HydrateModel
{
    public string $commandSignature = 'search:products {organisations?*} {--s|slugs=}';


    public function handle(Product $product): void
    {
        ProductRecordSearch::run($product);
    }

    protected function getModel(string $slug): Product
    {
        return Product::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Product::withTrashed()->get();
    }

    protected function loopAll(Command $command): void
    {
        $command->info("Reindex Products");
        $count = Product::withTrashed()->count();

        $bar = $command->getOutput()->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();

        Product::withTrashed()->chunk(1000, function (Collection $models) use ($bar) {
            foreach ($models as $model) {
                $this->handle($model);
                $bar->advance();
            }
        });

        $bar->finish();
        $command->info("");
    }
}
