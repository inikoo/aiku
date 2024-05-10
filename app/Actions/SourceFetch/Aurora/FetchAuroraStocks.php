<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 00:35:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Goods\Stock\StoreStock;
use App\Actions\Goods\Stock\SyncStockTradeUnits;
use App\Actions\Goods\Stock\UpdateStock;
use App\Actions\Inventory\OrgStock\StoreOrgStock;
use App\Actions\Inventory\OrgStock\SyncOrgStockLocations;
use App\Actions\Inventory\OrgStock\UpdateOrgStock;
use App\Models\Inventory\OrgStock;
use App\Models\SupplyChain\Stock;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraStocks extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:stocks {organisations?*} {--s|source_id=} {--N|only_new : Fetch only new} {--d|db_suffix=} {--r|reset}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): array
    {

        $stock   =null;
        $orgStock=null;

        if ($stockData = $organisationSource->fetchStock($organisationSourceId)) {


            if ($baseStock = Stock::withTrashed()->where('source_slug', $stockData['stock']['source_slug'])->first()) {
                if ($stock = Stock::withTrashed()->where('source_id', $stockData['stock']['source_id'])->first()) {
                    $stock = UpdateStock::make()->action(
                        stock: $stock,
                        modelData: $stockData['stock'],
                    );
                }
            } else {

                $stock = StoreStock::make()->action(
                    group: $organisationSource->getOrganisation()->group,
                    modelData: $stockData['stock'],
                    hydratorDelay: 30
                );
            }

            if ($stock) {
                $tradeUnit = $stockData['trade_unit'];

                SyncStockTradeUnits::run($stock, [
                    $tradeUnit->id => [
                        'quantity' => $stockData['stock']['units_per_pack']
                    ]
                ]);

                $sourceData = explode(':', $stock->source_id);

                DB::connection('aurora')
                    ->table('Part Dimension')
                    ->where('Part SKU', $sourceData[1])
                    ->update(['aiku_id' => $stock->id]);
            }

            $effectiveStock = $stock ?? $baseStock;

            $organisation = $organisationSource->getOrganisation();

            if($effectiveStock) {

                /** @var OrgStock $orgStock */
                if ($orgStock = $organisation->orgStocks()->where('source_id', $stockData['stock']['source_id'])->first()) {
                    $orgStock = UpdateOrgStock::make()->action(
                        orgStock: $orgStock,
                        modelData: $stockData['org_stock'],
                        hydratorDelay: 30
                    );
                } else {

                    $orgStock = StoreOrgStock::make()->action(
                        organisation: $organisationSource->getOrganisation(),
                        stock: $effectiveStock,
                        modelData: $stockData['org_stock'],
                        hydratorDelay: 30
                    );
                }

                $sourceData    = explode(':', $stockData['stock']['source_id']);
                $locationsData = $organisationSource->fetchLocationStocks($sourceData[1]);



                SyncOrgStockLocations::run($orgStock, $locationsData['stock_locations']);
            }

        }


        return [
            'stock'    => $stock,
            'orgStock' => $orgStock
        ];
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
