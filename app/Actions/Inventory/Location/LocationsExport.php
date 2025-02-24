<?php
/*
 * author Arya Permana - Kirin
 * created on 24-02-2025-13h-07m
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
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LocationsExport implements FromQuery, WithMapping, WithHeadings 
{
    use Exportable;

    private Warehouse $warehouse;
    private array $columns;
    
    public function __construct(Warehouse $warehouse, array $columns)
    {
        $this->warehouse = $warehouse;
        $this->columns = $columns;
    }


    public function map($row): array
    { 
        return collect($this->columns)->map(fn($column) => $row->{$column})->toArray();
    }

    public function headings(): array
    {
        return array_map(fn($col) => str_replace('_', ' ', ucfirst($col)), $this->columns);
    }
    public function query()
    {
        return Location::query()->where('warehouse_id', $this->warehouse->id);
    }
}