<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 20 Jun 2023 15:21:51 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Exports\Inventory;

use App\Models\Inventory\Location;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LocationsExport implements FromQuery, WithMapping, ShouldAutoSize, WithHeadings
{
    public function query(): Relation|\Illuminate\Database\Eloquent\Builder|Location|Builder
    {
        return Location::query();
    }

    /** @var Location $row */
    public function map($row): array
    {
        return [
            $row->id,
            $row->slug,
            $row->warehouse->name,
            isset($row->warehouseArea) ? $row->warehouseArea->name : null,
            $row->stock_value,
            $row->is_empty,
            $row->status->value,
            $row->created_at
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Slug',
            'Warehouse Name',
            'Warehouse Area Name',
            'Stock Value',
            'Is Empty',
            'Status',
            'Created At'
        ];
    }
}
