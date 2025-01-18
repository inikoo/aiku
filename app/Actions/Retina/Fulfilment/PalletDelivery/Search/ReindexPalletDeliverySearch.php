<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 07-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Retina\Fulfilment\PalletDelivery\Search;

use App\Actions\HydrateModel;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ReindexPalletDeliverySearch extends HydrateModel
{
    public string $commandSignature = 'search:retina_pallet_deliveries {organisations?*} {--s|slugs=}';


    public function handle(PalletDelivery $palletDelivery): void
    {
        PalletDeliveryRecordSearch::run($palletDelivery);
    }

    protected function getModel(string $slug): PalletDelivery
    {
        return PalletDelivery::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return PalletDelivery::withTrashed()->get();
    }

    protected function loopAll(Command $command): void
    {
        $command->info("Reindex Pallet Deliveries");
        $count = PalletDelivery::withTrashed()->count();

        $bar = $command->getOutput()->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();

        PalletDelivery::withTrashed()->chunk(1000, function (Collection $models) use ($bar) {
            foreach ($models as $model) {
                $this->handle($model);
                $bar->advance();
            }
        });

        $bar->finish();
        $command->info("");
    }
}
