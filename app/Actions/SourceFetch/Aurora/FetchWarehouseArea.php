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
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchWarehouseArea extends FetchModel
{


    public string $commandSignature = 'fetch:warehouse-area {organisation_code} {organisation_source_id?}';

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?WarehouseArea
    {
        if ($warehouseAreaData = $organisationSource->fetchWarehouseArea($organisationSourceId)) {
            if ($warehouseArea = WarehouseArea::where('organisation_source_id', $warehouseAreaData['warehouse_area']['organisation_source_id'])
                ->where('organisation_id', $organisationSource->organisation->id)
                ->first()) {
                $res = UpdateWarehouseArea::run(
                    warehouseArea: $warehouseArea,
                    modelData:     $warehouseAreaData['warehouse_area']
                );
            } else {
                $res = StoreWarehouseArea::run(
                    warehouse: $warehouseAreaData['warehouse'],
                    modelData: $warehouseAreaData['warehouse_area'],
                );
            }
            $this->progressBar?->advance();

            return $res->model;
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
