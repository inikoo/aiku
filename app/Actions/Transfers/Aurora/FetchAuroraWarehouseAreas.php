<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 01:44:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Inventory\WarehouseArea\StoreWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UpdateWarehouseArea;
use App\Models\Inventory\WarehouseArea;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraWarehouseAreas extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:warehouse-areas {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?WarehouseArea
    {
        setPermissionsTeamId($organisationSource->getOrganisation()->group_id);
        if ($warehouseAreaData = $organisationSource->fetchWarehouseArea($organisationSourceId)) {
            if ($warehouseArea = WarehouseArea::withTrashed()->where('source_id', $warehouseAreaData['warehouse_area']['source_id'])
                ->first()) {
                $warehouseArea = UpdateWarehouseArea::make()->action(
                    warehouseArea: $warehouseArea,
                    modelData: $warehouseAreaData['warehouse_area'],
                    hydratorsDelay: $this->hydratorsDelay,
                    strict: false,
                    audit: false
                );
            } else {
                try {
                    $warehouseArea = StoreWarehouseArea::make()->action(
                        warehouse: $warehouseAreaData['warehouse'],
                        modelData: $warehouseAreaData['warehouse_area'],
                        hydratorsDelay: $this->hydratorsDelay,
                        strict: false,
                        audit: false
                    );

                    WarehouseArea::enableAuditing();
                    $this->saveMigrationHistory(
                        $warehouseArea,
                        Arr::except($warehouseAreaData['warehouse_area'], ['fetched_at', 'last_fetched_at', 'source_id'])
                    );

                    $this->recordNew($organisationSource);

                    $sourceData = explode(':', $warehouseArea->source_id);
                    DB::connection('aurora')->table('Warehouse Area Dimension')
                        ->where('Warehouse Area Key', $sourceData[1])
                        ->update(['aiku_id' => $warehouseArea->id]);
                } catch (Exception|Throwable $e) {
                    $this->recordError($organisationSource, $e, $warehouseAreaData['warehouse_area'], 'WarehouseArea', 'store');

                    return null;
                }
            }

            return $warehouseArea;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Warehouse Area Dimension')
            ->select('Warehouse Area Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Warehouse Area Dimension')->count();
    }
}
