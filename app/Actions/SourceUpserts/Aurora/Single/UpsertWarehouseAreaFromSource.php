<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:24:50 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceUpserts\Aurora\Single;


use App\Actions\Inventory\WarehouseArea\StoreWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UpdateWarehouseArea;
use App\Models\Inventory\WarehouseArea;
use App\Services\Organisation\SourceOrganisationService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class UpsertWarehouseAreaFromSource
{
    use AsAction;
    use WithSingleFromSourceCommand;

    public string $commandSignature = 'source-update:warehouse-area {organisation_code} {organisation_source_id}';

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id): ?WarehouseArea
    {
        if ($warehouseAreaData = $organisationSource->fetchWarehouseArea($organisation_source_id)) {
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
            return $res->model;
        }

        return null;
    }


}
