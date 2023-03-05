<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Feb 2023 14:38:21 Malaysia Time,  Ubud, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Inventory\Stock\StoreStock;
use App\Actions\Inventory\Stock\UpdateStock;
use App\Models\Inventory\Stock;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchDeletedStocks extends FetchAction
{
    public string $commandSignature = 'fetch:deleted-stocks {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Stock
    {
        if ($deletedStockData = $tenantSource->fetchDeletedStock($tenantSourceId)) {
            if ($deletedStockData['stock']) {
                if ($stock = Stock::withTrashed()->where('source_id', $deletedStockData['stock']['source_id'])
                    ->first()) {
                    $stock = UpdateStock::run(
                        stock:     $stock,
                        modelData: $deletedStockData['stock'],
                    );
                } else {
                    $stock = StoreStock::run(
                        owner:     $tenantSource->tenant,
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
