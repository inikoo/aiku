<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 18-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\Banner\Search;

use App\Actions\HydrateModel;
use App\Models\Web\Banner;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ReindexBannerSearch extends HydrateModel
{
    public string $commandSignature = 'search:banners {organisations?*} {--s|slugs=} ';


    public function handle(Banner $banner): void
    {
        BannerRecordSearch::run($banner);
    }

    protected function getModel(string $slug): Banner
    {
        return Banner::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Banner::withTrashed()->get();
    }

    protected function loopAll(Command $command): void
    {
        $command->info("Reindex Banners");
        $count = Banner::withTrashed()->count();

        $bar = $command->getOutput()->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();

        Banner::withTrashed()->chunk(1000, function (Collection $models) use ($bar) {
            foreach ($models as $model) {
                $this->handle($model);
                $bar->advance();
            }
        });

        $bar->finish();
        $command->info("");
    }
}
