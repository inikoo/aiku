<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Feb 2023 14:38:21 Malaysia Time,  Ubud, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Goods\Stock\StoreStock;
use App\Actions\Goods\Stock\UpdateStock;
use App\Actions\Inventory\OrgStock\StoreOrgStock;
use App\Actions\Inventory\OrgStock\SyncOrgStockLocations;
use App\Actions\Inventory\OrgStock\UpdateOrgStock;
use App\Models\Inventory\OrgStock;
use App\Models\SupplyChain\Stock;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraDeletedStocks extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:deleted-stocks {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): array
    {

        $stock   =null;
        $orgStock=null;

        if ($stockData = $organisationSource->fetchDeletedStock($organisationSourceId)) {


            if ($baseStock = Stock::withTrashed()->where('source_slug', $stockData['stock']['source_slug'])->first()) {
                if ($stock = Stock::withTrashed()->where('source_id', $stockData['stock']['source_id'])->first()) {
                    $stock = UpdateStock::make()->action(
                        stock: $stock,
                        modelData: $stockData['stock'],
                    );
                }
            } else {

                $stock = StoreStock::make()->action(
                    parent: $organisationSource->getOrganisation()->group,
                    modelData: $stockData['stock'],
                    hydratorDelay: 30
                );
            }

            if ($stock) {


                $sourceData = explode(':', $stock->source_id);

                DB::connection('aurora')
                    ->table('Part Deleted Dimension')
                    ->where('Part Deleted KEY', $sourceData[1])
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
        return DB::connection('aurora')
            ->table('Part Deleted Dimension')
            ->select('Part Deleted Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Part Deleted Dimension')->count();
    }
}
