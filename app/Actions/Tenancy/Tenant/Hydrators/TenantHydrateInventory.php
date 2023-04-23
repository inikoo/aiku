<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant\Hydrators;

use App\Enums\Inventory\Stock\StockQuantityStatusEnum;
use App\Enums\Inventory\Stock\StockStateEnum;
use App\Enums\Inventory\StockFamily\StockFamilyStateEnum;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;
use App\Models\Tenancy\Tenant;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class TenantHydrateInventory implements ShouldBeUnique
{
    use AsAction;
    use HasTenantHydrate;

    public function handle(Tenant $tenant): void
    {
        $stats  = [
            'number_stocks'         => Stock::count(),
            'number_stock_families' => StockFamily::count(),
        ];

        $stockFamilyStateCount = StockFamily::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach (StockFamilyStateEnum::cases() as $stockFamilyState) {
            $stats['number_stock_families_state_'.$stockFamilyState->snake()] = Arr::get($stockFamilyStateCount, $stockFamilyState->value, 0);
        }


        $stockStateCount = Stock::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach (StockStateEnum::cases() as $stockState) {
            $stats['number_stocks_state_'.$stockState->snake()] = Arr::get($stockStateCount, $stockState->value, 0);
        }

        $stockQuantityStatusCount = Stock::selectRaw('quantity_status, count(*) as total')
            ->groupBy('quantity_status')
            ->pluck('total', 'quantity_status')->all();


        foreach (StockQuantityStatusEnum::cases() as $stockQuantityStatus) {
            $stats['number_stocks_quantity_status_'.$stockQuantityStatus->snake()] = Arr::get($stockQuantityStatusCount, $stockQuantityStatus->value, 0);
        }


        $tenant->inventoryStats->update($stats);
    }
}
