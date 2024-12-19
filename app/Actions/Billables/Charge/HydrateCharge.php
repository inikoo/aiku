<?php
/*
 * author Arya Permana - Kirin
 * created on 19-12-2024-11h-33m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Billables\Charge;

use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateInvoicedCustomers;
use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateInvoices;
use App\Actions\HydrateModel;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Models\Catalogue\Asset;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class HydrateCharge extends HydrateModel
{
    public string $commandSignature = 'hydrate:charges {organisations?*} {--slugs=} ';


    public function handle(Asset $asset): void
    {
        if ($asset->type == AssetTypeEnum::CHARGE) {
            AssetHydrateInvoices::run($asset);
            AssetHydrateInvoicedCustomers::run($asset);
        }

    }


    protected function getModel(string $slug): Asset
    {
        return Asset::where('slug', $slug)->first();
    }

    protected function loopAll(Command $command): void
    {
        $command->info("Hydrating assets");
        $count = Asset::withTrashed()->count();

        $bar = $command->getOutput()->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();

        Asset::withTrashed()->chunk(1000, function (Collection $models) use ($bar) {
            foreach ($models as $model) {
                $this->handle($model);
                $bar->advance();
            }
        });

        $bar->finish();
        $command->info("");
    }
}
