<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 09 Sept 2024 14:46:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Stock;

use App\Actions\Goods\Stock\Hydrators\StockHydrateWeightFromTradeUnits;
use App\Actions\HydrateModel;
use App\Models\SupplyChain\Stock;
use Illuminate\Console\Command;

class HydrateStocks extends HydrateModel
{
    public string $commandSignature = 'hydrate:stocks';


    public function handle(Stock $stock): void
    {
        StockHydrateWeightFromTradeUnits::run($stock);
    }

    public function asCommand(Command $command): int
    {


        $command->withProgressBar(Stock::all(), function ($supplier) {
            $this->handle($supplier);
        });
        $command->info("");

        return 0;
    }
}
