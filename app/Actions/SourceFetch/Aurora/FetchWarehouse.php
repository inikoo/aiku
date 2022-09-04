<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 01:31:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Inventory\Warehouse\StoreWarehouse;
use App\Actions\Inventory\Warehouse\UpdateWarehouse;
use App\Models\Inventory\Warehouse;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchWarehouse extends FetchModel
{

    public string $commandSignature = 'fetch:warehouses {organisation_code} {organisation_source_id?}';

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id): ?Warehouse
    {
        if ($warehouseData = $organisationSource->fetchWarehouse($organisation_source_id)) {
            if ($warehouse = Warehouse::where('organisation_source_id', $warehouseData['warehouse']['organisation_source_id'])
                ->where('organisation_id', $organisationSource->organisation->id)
                ->first()) {
                $res = UpdateWarehouse::run(
                    warehouse: $warehouse,
                    modelData: $warehouseData['warehouse']
                );
            } else {
                $res = StoreWarehouse::run(
                    organisation: $organisationSource->organisation,
                    modelData:    $warehouseData['warehouse'],
                );
            }
            $this->progressBar?->advance();

            return $res->model;
        }

        return null;
    }

    function fetchAll(SourceOrganisationService $organisationSource): void
    {
        foreach (
            DB::connection('aurora')
                ->table('Warehouse Dimension')
                ->select('Warehouse Key')
                ->get() as $auroraData
        ) {
            $this->handle($organisationSource, $auroraData->{'Warehouse Key'});
        }
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Warehouse Dimension')->count();
    }

}
