<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 20 Jun 2023 09:17:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Exports\Procurement;

use App\Models\SupplyChain\Supplier;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SuppliersExport implements FromQuery, WithMapping, ShouldAutoSize, WithHeadings
{
    public function query(): Relation|\Illuminate\Database\Eloquent\Builder|\App\Models\SupplyChain\Supplier|Builder
    {
        return Supplier::query();
    }

    /** @var Supplier $row */
    public function map($row): array
    {
        return [
            $row->id,
            $row->slug,
            !isset($row->agent),
            isset($row->agent) ? $row->agent->name : null,
            $row->name,
            $row->email,
            $row->phone,
            $row->contact_name,
            $row->currency->code,
            $row->type,
            $row->location,
            $row->created_at
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Slug',
            'Independent Supplier',
            'Agent Name',
            'Name',
            'Email',
            'Phone',
            'Contact Name',
            'Currency',
            'Type',
            'Location',
            'Created At'
        ];
    }
}
