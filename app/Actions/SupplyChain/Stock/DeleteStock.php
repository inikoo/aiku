<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 11:09:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Stock;

use App\Actions\SupplyChain\StockFamily\Hydrators\StockFamilyHydrateStocks;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateSupplyChain;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithOrganisationArgument;
use App\Models\SupplyChain\Stock;
use Exception;
use Illuminate\Console\Command;

class DeleteStock
{
    use WithActionUpdate;
    use WithOrganisationArgument;

    public string $commandSignature = 'delete:stock {stock}';

    public function handle(Stock $stock, array $deletedData = [], bool $skipHydrate = false): Stock
    {
        $stock->delete();
        $stock = $this->update($stock, $deletedData, ['data']);
        //Todo: PKA-18
        if (!$skipHydrate) {
            GroupHydrateSupplyChain::dispatch(group());
            if ($stock->stock_family_id) {
                StockFamilyHydrateStocks::dispatch($stock->stockFamily);
            }
        }

        return $stock;
    }


    public function asCommand(Command $command): int
    {

        try {
            $stock= Stock::findOrFail($command->argument('stock'));
        } catch (Exception $e) {
            $command->error('Stock not found '.$e->getMessage());
            return 1;
        }

        $this->handle($stock);

        return 0;
    }
}
