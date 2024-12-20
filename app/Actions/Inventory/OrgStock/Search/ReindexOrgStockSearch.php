<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 14-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Inventory\OrgStock\Search;

use App\Actions\HydrateModel;
use App\Models\Inventory\Location;
use App\Models\Inventory\OrgStock;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ReindexOrgStockSearch extends HydrateModel
{
    public string $commandSignature = 'search:org_stocks {organisations?*} {--s|slugs=}';


    public function handle(OrgStock $orgStock): void
    {
        OrgStockRecordSearch::run($orgStock);
    }


    protected function getModel(string $slug): OrgStock
    {
        return OrgStock::withTrashed()->where('slug', $slug)->first();
    }

    protected function loopAll(Command $command): void
    {
        $command->info("Reindex Org Stocks");
        $count = OrgStock::withTrashed()->count();

        $bar = $command->getOutput()->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();

        OrgStock::withTrashed()->chunk(1000, function (Collection $models) use ($bar) {
            foreach ($models as $model) {
                $this->handle($model);
                $bar->advance();
            }
        });

        $bar->finish();
        $command->info("");
    }
}
