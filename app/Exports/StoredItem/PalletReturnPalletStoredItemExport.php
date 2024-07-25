<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jul 2023 12:40:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Exports\StoredItem;

use App\Models\Fulfilment\PalletStoredItem;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PalletReturnPalletStoredItemExport implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping
{
    use Exportable;

    public function query()
    {
        return PalletStoredItem::query();
    }

    public function map($row): array
    {
        /** @var PalletStoredItem $row */
        $palletStoredItem = $row;
        return [
            $palletStoredItem->id,
            $palletStoredItem->pallet->reference,
            $palletStoredItem->storedItem->reference,
            $palletStoredItem->quantity
        ];
    }

    public function headings(): array
    {
        return [
            'Id',
            'Pallet',
            'Stored Item',
            'Stored Item Quantity',
            'Pick Stored Item',
        ];
    }
}
