<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Feb 2023 14:38:21 Malaysia Time,  Ubud, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Goods\Stock\StoreStock;
use App\Actions\Goods\Stock\UpdateStock;
use App\Models\SupplyChain\Stock;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraDeletedStocks extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:deleted-stocks {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): array
    {

        $stock   =null;
        $orgStock=null;

        if ($deletedStockData = $organisationSource->fetchDeletedStock($organisationSourceId)) {
            if ($deletedStockData['stock']) {
                if ($stock = Stock::withTrashed()->where('source_id', $deletedStockData['stock']['source_id'])
                    ->first()) {
                    $stock = UpdateStock::run(
                        stock:     $stock,
                        modelData: $deletedStockData['stock'],
                    );
                } else {
                    $stock = StoreStock::run(
                        group:     $organisationSource->getOrganisation()->group,
                        modelData: $deletedStockData['stock']
                    );
                }
                $sourceData = explode(':', $stock->source_id);

                DB::connection('aurora')->table('Part Deleted Dimension')
                    ->where('Part Deleted Key', $sourceData[1])
                    ->update(['aiku_id' => $stock->id]);

                return $stock;
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
