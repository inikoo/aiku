<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Aug 2024 22:13:51 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory\Search;

use App\Actions\HydrateModel;
use App\Models\Catalogue\ProductCategory;
use App\Models\CRM\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ReindexProductCategorySearch extends HydrateModel
{
    public string $commandSignature = 'search:product_categories {organisations?*} {--s|slugs=}';


    public function handle(ProductCategory $productCategory): void
    {
        ProductCategoryRecordSearch::run($productCategory);
    }

    protected function getModel(string $slug): ProductCategory
    {
        return ProductCategory::withTrashed()->where('slug', $slug)->first();
    }
    
    protected function loopAll(Command $command): void
    {
        $count = ProductCategory::withTrashed()->count();

        $bar = $command->getOutput()->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();

        ProductCategory::withTrashed()->chunk(1000, function (Collection $models) use ($bar) {
            foreach ($models as $model) {
                $this->handle($model);
                $bar->advance();
            }
        });


        $bar->finish();


        $command->info("");
    }
    
}
