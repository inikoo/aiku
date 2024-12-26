<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 09 Sept 2024 14:46:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Stock;

use App\Actions\Goods\Stock\Hydrators\StockHydrateGrossWeightFromTradeUnits;
use App\Actions\HydrateModel;
use App\Models\Goods\Stock;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class HydrateStocks extends HydrateModel
{
    public string $commandSignature = 'hydrate:stocks';


    public function handle(Stock $stock): void
    {
        StockHydrateGrossWeightFromTradeUnits::run($stock);
    }

    public function asCommand(Command $command): int
    {
        $count = Stock::count();
        $bar   = $command->getOutput()->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();
        Stock::chunk(1000, function (Collection $models) use ($bar) {
            foreach ($models as $model) {
                $this->handle($model);
                $bar->advance();
            }
        });
        $bar->finish();


        return 0;
    }
}
