<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\StockFamily;

use App\Actions\Goods\StockFamily\Hydrators\StockFamilyHydrateStocks;
use App\Models\SupplyChain\StockFamily;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class HydrateStockFamily
{
    use AsAction;
    public string $commandSignature = 'stock-family:hydrate {--s|slug=}';

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
