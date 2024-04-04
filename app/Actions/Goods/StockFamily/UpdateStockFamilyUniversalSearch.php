<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 18:45:27 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\StockFamily;

use App\Actions\Goods\StockFamily\Hydrators\StockFamilyHydrateUniversalSearch;
use App\Models\SupplyChain\StockFamily;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateStockFamilyUniversalSearch
{
    use asAction;

    public string $commandSignature = 'stock-families:search';

    public function handle(StockFamily $stockFamily): void
    {
        StockFamilyHydrateUniversalSearch::run($stockFamily);
    }

    public function asCommand(Command $command): int
    {

        $command->withProgressBar(StockFamily::all(), function (StockFamily $stockFamily) {
            $this->handle($stockFamily);
        });
        return 0;
    }
}
