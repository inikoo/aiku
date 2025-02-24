<?php
/*
 * author Arya Permana - Kirin
 * created on 24-02-2025-13h-09m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Inventory\Location;

use App\Actions\OrgAction;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;
use App\Models\Inventory\Location;
use App\Models\SysAdmin\Organisation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;

class DownloadLocations extends OrgAction 
{
    public function handle(Warehouse $warehouse, array $modelData)
    {
        $fileName = 'locations_warehouse_' . $warehouse->id . '.xlsx';
        return Excel::download(new LocationsExport($warehouse, $modelData), $fileName);
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, ActionRequest $request)
    {
        $this->initialisationFromWarehouse($warehouse, $request);
        $columns = explode(',', $request->query('columns', ''));
        
        return $this->handle($warehouse, $columns);
    }
}