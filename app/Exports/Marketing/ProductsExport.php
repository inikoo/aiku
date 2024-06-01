<?php

namespace App\Exports\Marketing;

use App\Models\Catalogue\Billable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromQuery, WithMapping, ShouldAutoSize, WithHeadings
{
    public function query(): Relation|\Illuminate\Database\Eloquent\Builder|Billable|Builder
    {
        return Billable::query();
    }

    /** @var Billable $row */
    public function map($row): array
    {
        return [
            $row->id,
            $row->slug,
            $row->type,
            $row->shop->name,
            $row->state,
            $row->status,
            $row->units,
            $row->price,
            $row->rrp,
            $row->available,
            $row->created_at
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Slug',
            'Type',
            'Shop Name',
            'State',
            'Status',
            'Units',
            'Price',
            'RRP',
            'Available',
            'Created At'
        ];
    }
}
