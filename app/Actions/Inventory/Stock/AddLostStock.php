<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 15 May 2023 16:45:46 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


namespace App\Actions\Inventory\Stock;

use App\Actions\Inventory\StockFamily\Hydrators\StockFamilyHydrateStocks;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateInventory;
use App\Actions\WithActionUpdate;
use App\Actions\WithTenantArgument;
use App\Models\Inventory\Stock;
use Illuminate\Console\Command;

class AddLostStock
{
    use WithActionUpdate;

    public function handle(Stock $stock, array $modelData = []): Stock
    {
        // TODO : Add Lost Stock
    }
}
