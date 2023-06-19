<?php

namespace App\Exports\User;

use App\Models\Auth\Guest;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GuestsExport implements FromQuery, WithMapping, ShouldAutoSize, WithHeadings
{
    public function query(): Relation|\Illuminate\Database\Eloquent\Builder|Guest|Builder
    {
        return Guest::query();
    }

    /** @var Guest $row */
    public function map($row): array
    {
        return [
            $row->id,
            $row->slug,
            $row->email,
            $row->contact_name,
            $row->phone,
            $row->type,
            $row->status
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Slug',
            'Email',
            'Contact Name',
            'Phone Number',
            'Type',
            'Status',
        ];
    }
}
