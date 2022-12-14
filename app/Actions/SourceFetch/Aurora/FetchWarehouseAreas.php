<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 01:44:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Inventory\WarehouseArea\StoreWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UpdateWarehouseArea;
use App\Models\Inventory\WarehouseArea;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchWarehouseAreas extends FetchAction
{


    public string $commandSignature = 'fetch:warehouse-areas {tenants?*} {--s|source_id=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?WarehouseArea
    {
        if ($warehouseAreaData = $tenantSource->fetchWarehouseArea($tenantSourceId)) {
            if ($warehouseArea = WarehouseArea::withTrashed()->where('source_id', $warehouseAreaData['warehouse_area']['source_id'])
                ->first()) {
                $warehouseArea = UpdateWarehouseArea::run(
                    warehouseArea: $warehouseArea,
                    modelData:     $warehouseAreaData['warehouse_area']
                );
            } else {
                $warehouseArea = StoreWarehouseArea::run(
                    warehouse: $warehouseAreaData['warehouse'],
                    modelData: $warehouseAreaData['warehouse_area'],
                );
            }


            return $warehouseArea;
        }

        return null;
    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Warehouse Area Dimension')
            ->select('Warehouse Area Key as source_id')
            ->orderBy('source_id');
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Warehouse Area Dimension')->count();
    }


}
