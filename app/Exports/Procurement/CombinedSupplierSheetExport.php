<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Exports\Procurement;

use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\SupplyChain\Supplier;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * One sheet for a factory with every client's open order on it, one block per order, in Chinese and English:
 * the factory makes the combined volume but packs, marks and invoices each client separately.
 */
class CombinedSupplierSheetExport implements FromArray, ShouldAutoSize, WithTitle
{
    public const array HEADINGS = [
        'AW code / 货号',
        'Supplier code / 供应商货号',
        'Description / 产品说明',
        'Cartons / 箱数',
        'Units per carton / 每箱数量',
        'Units / 总数量',
        'Unit price / 单价',
        'Amount / 总金额',
        'Carton mark / 箱唛',
    ];

    /**
     * @param  Collection<int, PurchaseOrder>  $purchaseOrders  with organisation, currency and purchaseOrderTransactions.supplierProduct and .orgStock loaded
     */
    public function __construct(protected Supplier $supplier, protected Collection $purchaseOrders)
    {
    }

    public static function cartonMark(PurchaseOrder $purchaseOrder): string
    {
        return $purchaseOrder->organisation->code.' · '.$purchaseOrder->reference;
    }

    public function array(): array
    {
        $rows = [
            [$this->supplier->name.' ('.$this->supplier->code.')'],
            ['Combined order / 合并订单', now()->toDateString()],
            [],
        ];

        foreach ($this->purchaseOrders as $purchaseOrder) {
            $mark  = self::cartonMark($purchaseOrder);
            $lines = $purchaseOrder->purchaseOrderTransactions
                ->sortBy(fn (PurchaseOrderTransaction $line) => $line->orgStock?->code)
                ->values();

            $rows[] = [
                $purchaseOrder->organisation->code.' · '.$purchaseOrder->organisation->name,
                $purchaseOrder->reference,
                $purchaseOrder->date?->toDateString(),
                $purchaseOrder->state->labels()[$purchaseOrder->state->value],
            ];
            $rows[] = self::HEADINGS;

            $cartons = 0.0;
            foreach ($lines as $line) {
                $unitsPerCarton = (float) $line->supplierProduct?->units_per_carton;
                $lineCartons    = $unitsPerCarton > 0 ? round((float) $line->quantity_ordered / $unitsPerCarton, 2) : null;
                $cartons       += (float) $lineCartons;

                $rows[] = [
                    $line->orgStock?->code,
                    $line->supplierProduct?->code,
                    $line->supplierProduct?->name ?? $line->orgStock?->name,
                    $lineCartons,
                    $unitsPerCarton ?: null,
                    (float) $line->quantity_ordered,
                    $line->unit_cost === null ? null : (float) $line->unit_cost,
                    (float) $line->net_amount,
                    $mark,
                ];
            }

            $rows[] = ['Total / 合计 '.$purchaseOrder->organisation->code, null, null, round($cartons, 2), null, (float) $lines->sum('quantity_ordered'), null, round((float) $lines->sum('net_amount'), 2), $purchaseOrder->currency?->code];
            $rows[] = [];
        }

        return $rows;
    }

    public function title(): string
    {
        return mb_substr($this->supplier->code, 0, 31);
    }
}
