<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 00:35:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Inventory\Stock\StoreStock;
use App\Actions\Inventory\Stock\SyncStockTradeUnits;
use App\Actions\Inventory\Stock\UpdateStock;
use App\Models\Inventory\Stock;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchStocks extends FetchAction
{
    public string $commandSignature = 'fetch:stocks {organisations?*} {--s|source_id=} {--N|only_new : Fetch only new} {--d|db_suffix=} {--r|reset}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Stock
    {
        if ($stockData = $organisationSource->fetchStock($organisationSourceId)) {
            if ($stock = Stock::withTrashed()->where('source_id', $stockData['stock']['source_id'])
                ->first()) {
                $stock = UpdateStock::run(
                    stock: $stock,
                    modelData: $stockData['stock'],
                );
            } else {
                $stock = StoreStock::run(
                    owner: $organisationSource->getOrganisation(),
                    modelData: $stockData['stock']
                );
            }
            $tradeUnit = $stockData['trade_unit'];

            SyncStockTradeUnits::run($stock, [
                $tradeUnit->id => [
                    'quantity' => $stockData['stock']['units_per_pack']
                ]
            ]);
            $locationsData = $organisationSource->fetchLocationStocks($organisationSourceId);

            $stock->locations()->sync($locationsData['stock_locations']);

            DB::connection('aurora')
                ->table('Part Dimension')
                ->where('Part SKU', $stock->source_id)
                ->update(['aiku_id' => $stock->id]);

            return $stock;
        }


        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Part Dimension')
            ->select('Part SKU as source_id')
            ->when(app()->environment('testing'), function ($query) {
                return $query->limit(20);
            });

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }
        $query->orderBy('source_id');

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Part Dimension');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }
        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Part Dimension')->update(['aiku_id' => null]);
    }
}
