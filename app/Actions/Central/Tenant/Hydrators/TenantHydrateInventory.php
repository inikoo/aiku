<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 16:35:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant\Hydrators;

use App\Models\Central\Tenant;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;
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

        $stockFamilyStates     = ['in-process', 'active', 'discontinuing', 'discontinued'];
        $stockFamilyStateCount = StockFamily::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach ($stockFamilyStates as $stockFamilyState) {
            $stats['number_stock_families_state_'.str_replace('-', '_', $stockFamilyState)] = Arr::get($stockFamilyStateCount, $stockFamilyState, 0);
        }

        $stockStates     = ['in-process', 'active', 'discontinuing', 'discontinued'];
        $stockStateCount = Stock::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach ($stockStates as $stockState) {
            $stats['number_stocks_state_'.str_replace('-', '_', $stockState)] = Arr::get($stockStateCount, $stockState, 0);
        }

        $tenant->inventoryStats->update($stats);
    }
}
