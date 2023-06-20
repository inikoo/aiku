<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 20 Jun 2023 09:17:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Exports\Procurement;

use App\Models\Procurement\Agent;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AgentsExport implements FromQuery, WithMapping, ShouldAutoSize, WithHeadings
{
    public function query(): Relation|\Illuminate\Database\Eloquent\Builder|Agent|Builder
    {
        return Agent::query();
    }

    /** @var Agent $row */
    public function map($row): array
    {
        return [
            $row->id,
            $row->slug,
            $row->name,
            $row->email,
            $row->phone,
            $row->owner->name,
            $row->contact_name,
            $row->currency->code,
            $row->location,
            $row->created_at
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Slug',
            'Name',
            'Email',
            'Phone',
            'Owner Name',
            'Contact Name',
            'Currency',
            'Location',
            'Created At'
        ];
    }
}
