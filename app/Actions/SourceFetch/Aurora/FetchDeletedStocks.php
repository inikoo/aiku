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
use JetBrains\PhpStorm\NoReturn;

class FetchDeletedStocks extends FetchAction
{
    public string $commandSignature = 'fetch:deleted-stocks {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Stock
    {
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

                DB::connection('aurora')->table('Part Deleted Dimension')
                    ->where('Part Deleted Key', $stock->source_id)
                    ->update(['aiku_id' => $stock->id]);

                return $stock;
            }
        }

        return null;
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
