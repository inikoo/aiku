<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Feb 2023 12:49:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Stock;

use App\Actions\Central\Tenant\Hydrators\TenantHydrateInventory;
use App\Actions\Inventory\StockFamily\Hydrators\StockFamilyHydrateStocks;
use App\Actions\WithActionUpdate;
use App\Actions\WithTenantArgument;
use App\Models\Inventory\Stock;
use Illuminate\Console\Command;

class DeleteStock
{
    use WithActionUpdate;
    use WithTenantArgument;

    public string $commandSignature = 'delete:stock {tenant} {id}';

    public function handle(Stock $stock, array $deletedData = [], bool $skipHydrate = false): Stock
    {
        $stock->delete();
        $stock = $this->update($stock, $deletedData, ['data']);
        //Todo: PKA-18
        if (!$skipHydrate) {
            TenantHydrateInventory::dispatch(app('currentTenant'));
            if ($stock->stock_family_id) {
                StockFamilyHydrateStocks::dispatch($stock->stockFamily);
            }
        }

        return $stock;
    }


    public function asCommand(Command $command): int
    {
        $this->getTenant($command)->execute(
            fn () => $this->handle(Stock::findOrFail($command->argument('id')))
        );

        return 0;
    }
}
