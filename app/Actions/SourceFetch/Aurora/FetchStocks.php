<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 00:35:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Inventory\Stock\StoreStock;
use App\Actions\Inventory\Stock\UpdateStock;
use App\Models\Inventory\Stock;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchStocks extends FetchAction
{

    public string $commandSignature = 'fetch:stocks {tenants?*} {--s|source_id=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Stock
    {
        if ($stockData = $tenantSource->fetchStock($tenantSourceId)) {
            if ($stock = Stock::where('source_id', $stockData['stock']['source_id'])
                ->first()) {
                $stock = UpdateStock::run(
                    stock:     $stock,
                    modelData: $stockData['stock'],
                );
            } else {
                $stock = StoreStock::run(
                    owner:     $tenantSource->tenant,
                    modelData: $stockData['stock']
                );
            }
            $tradeUnit = FetchTradeUnits::run($tenantSource, $stock->source_id);
            $stock->tradeUnits()->sync([
                                           $tradeUnit->id => [
                                               'quantity' => $stockData['units_per_package']
                                           ]
                                       ]);

            $locationsData = $tenantSource->fetchStockLocations($tenantSourceId);

            $stock->locations()->sync($locationsData['stock_locations']);

            $this->progressBar?->advance();

            return $stock;
        }


        return null;
    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Part Dimension')
            ->select('Part SKU as source_id')
            ->where('Part Status', '!=', 'Not In Use')
            ->orderBy('source_id');
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Part Dimension')->where('Part Status', '!=', 'Not In Use')->count();
    }

}
