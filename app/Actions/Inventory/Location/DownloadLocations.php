<?php

/*
 * author Arya Permana - Kirin
 * created on 24-02-2025-13h-09m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Inventory\Location;

use App\Actions\OrgAction;
use App\Events\FileDownloadProgress;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\ActionRequest;
use App\Models\SysAdmin\Organisation;
use Maatwebsite\Excel\Facades\Excel;

class DownloadLocations extends OrgAction
{
    public function handle(Warehouse $warehouse, array $modelData): void
    {
        $fileName = 'locations_warehouse_' . $warehouse->id . '.xlsx';

        Excel::queue(new LocationsExport($warehouse, $modelData), 'public/'.$fileName)->chain([
            function () use ($warehouse, $fileName) {
                broadcast(new FileDownloadProgress($warehouse->id, 100, $fileName));
            }
        ]);
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): void
    {
        $this->initialisationFromWarehouse($warehouse, $request);
        $columns = explode(',', $request->query('columns', ''));

        $this->handle($warehouse, $columns);
    }
}
