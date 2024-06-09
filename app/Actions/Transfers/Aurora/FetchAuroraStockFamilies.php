<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 11:01:21 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Goods\StockFamily\StoreStockFamily;
use App\Actions\Goods\StockFamily\UpdateStockFamily;
use App\Models\SupplyChain\StockFamily;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraStockFamilies extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:stock-families {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?StockFamily
    {
        if ($stockFamilyData = $organisationSource->fetchStockFamily($organisationSourceId)) {

            if ($baseStockFamily = StockFamily::withTrashed()->where('source_slug', $stockFamilyData['stock_family']['source_slug'])->first()) {
                if ($stockFamily = StockFamily::where('source_id', $stockFamilyData['stock_family']['source_id'])
                    ->first()) {
                    $stockFamily = UpdateStockFamily::make()->action(
                        stockFamily: $stockFamily,
                        modelData: $stockFamilyData['stock_family'],
                    );
                }
            } else {
                $stockFamily = StoreStockFamily::make()->action(
                    group: $organisationSource->getOrganisation()->group,
                    modelData: $stockFamilyData['stock_family']
                );
            }


            return $stockFamily ?? $baseStockFamily;
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
