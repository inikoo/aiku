<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 11:01:21 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Inventory\StockFamily\StoreStockFamily;
use App\Actions\Inventory\StockFamily\UpdateStockFamily;
use App\Models\Inventory\StockFamily;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchStockFamilies extends FetchAction
{
    public string $commandSignature = 'fetch:stock-families {tenants?*} {--s|source_id=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?StockFamily
    {
        if ($stockFamilyData = $tenantSource->fetchStockFamily($tenantSourceId)) {
            if ($stockFamily = StockFamily::where('source_id', $stockFamilyData['stock_family']['source_id'])
                ->first()) {
                $stockFamily = UpdateStockFamily::run(
                    stockFamily: $stockFamily,
                    modelData:   $stockFamilyData['stock_family'],
                );
            } else {
                $stockFamily = StoreStockFamily::run(
                    modelData: $stockFamilyData['stock_family']
                );
            }

            return $stockFamily;
        }


        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Category Dimension')
            ->leftJoin('Part Category Dimension', 'Category Key', 'Part Category Key')
            ->select('Category Key as source_id')
            ->where('Category Branch Type', 'Head')
            ->where('Category Scope', 'Part')
            ->orderBy('source_id')
            ->when(app()->environment('testing'), function ($query) {
                return $query->limit(20);
            });
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Category Dimension')
            ->where('Category Branch Type', 'Head')
            ->where('Category Scope', 'Part')
            ->count();
    }
}
