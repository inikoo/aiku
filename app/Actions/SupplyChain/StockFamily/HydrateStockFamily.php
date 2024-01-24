<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 13:06:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\StockFamily;

use App\Actions\HydrateModel;
use App\Actions\SupplyChain\StockFamily\Hydrators\StockFamilyHydrateStocks;
use App\Models\SupplyChain\StockFamily;
use Exception;
use Illuminate\Console\Command;

class HydrateStockFamily extends HydrateModel
{
    public string $commandSignature = 'stock-family:hydrate {--s|slug=} ';

    public function handle(StockFamily $stockFamily): void
    {
        StockFamilyHydrateStocks::run($stockFamily);

    }


    public function asCommand(Command $command): int
    {


        if($command->option('slug')) {
            try {
                $stockFamily = StockFamily::where('slug', $command->option('slug'))->firstorFail();
                $this->handle($stockFamily);
                return 0;
            } catch (Exception $e) {
                $command->error($e->getMessage());
                return 1;
            }
        } else {
            $command->withProgressBar(StockFamily::withTrashed()->get(), function ($stockFamily) {
                if ($stockFamily) {
                    $this->handle($stockFamily);
                }
            });
            $command->info("");
        }

        return 0;
    }


}
