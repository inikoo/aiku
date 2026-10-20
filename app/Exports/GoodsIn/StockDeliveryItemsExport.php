<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Exports\GoodsIn;

use App\Actions\GoodsIn\StockDelivery\PdfStockDelivery;
use App\Models\GoodsIn\StockDelivery;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class StockDeliveryItemsExport implements FromArray, WithHeadings, WithStrictNullComparison
{
    public function __construct(protected StockDelivery $stockDelivery)
    {
    }

    public function array(): array
    {
        $columns = $this->columns();

        return PdfStockDelivery::rows($this->stockDelivery)
            ->map(fn (array $row) => array_values(Arr::only($row, $columns)))
            ->all();
    }

    public function headings(): array
    {
        return $this->columns();
    }

    /**
     * @return list<string>
     */
    private function columns(): array
    {
        return array_values(array_filter(
            ['code', 'name', 'units', 'skos', 'cartons', 'cbm', 'packed_inside', 'contains'],
            fn (string $column) => $column !== 'cbm' || PdfStockDelivery::showsCbm($this->stockDelivery)
        ));
    }
}
