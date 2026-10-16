<?php

/*
 * Author Louis Perez
 * Created on 17-10-2026-23h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentTargetFieldEnum;
use App\Enums\Goods\TradeUnit\TradeUnitLabelPresenceEnum;
use App\Enums\Goods\TradeUnit\TradeUnitMarketEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentChecklistItem;
use App\Models\Goods\ProductDevelopmentItem;
use App\Models\Goods\Stock;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * What the designer needs to lay out a SKU's label: the pack text compliance has approved, read from
 * the trade unit, and the three barcodes (unit EAN-13, SKO and carton Code 128).
 */
class GetProductDevelopmentLabelContent
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(ProductDevelopment $productDevelopment, ProductDevelopmentItem $item): array
    {
        $tradeUnit  = $item->tradeUnit;
        $compliance = $productDevelopment->checklistItems()
            ->where('product_development_item_id', $item->id)
            ->where('role', ProductDevelopmentMemberRoleEnum::COMPLIANCE->value)
            ->get();
        $missing = $compliance->filter(fn (ProductDevelopmentChecklistItem $checklistItem) => $checklistItem->isMissing())->pluck('label')->values()->all();

        /** @var Stock|null $stock */
        $stock      = $tradeUnit->stocks()->orderBy('stocks.id')->first();
        $labelInfo  = $tradeUnit->label_info ?? [];
        $pictograms = collect(ProductDevelopmentTargetFieldEnum::pictograms())->filter(fn (array $pictogram, string $key) => (bool) $tradeUnit->getAttribute($key))->values()->all();

        return [
            'sku'        => ['code' => $tradeUnit->code, 'name' => $tradeUnit->name],
            'approved'   => $missing === [] && $compliance->isNotEmpty(),
            'missing'    => $missing,
            'text'       => array_values(array_filter([
                ['label' => __('Product name'), 'value' => $tradeUnit->name],
                ['label' => __('Ingredients'), 'value' => $tradeUnit->ingredients()->orderBy('name')->pluck('name')->implode(', ') ?: null],
                ['label' => __('Warnings & precautions'), 'value' => $tradeUnit->gpsr_warnings],
                ['label' => __('Manufacturer'), 'value' => $tradeUnit->gpsr_manufacturer],
                ['label' => __('EU responsible person'), 'value' => $tradeUnit->gpsr_eu_responsible],
                ['label' => __('UFI'), 'value' => $tradeUnit->ufi_number],
                ['label' => __('UN number'), 'value' => $tradeUnit->un_number],
                ['label' => __('Markets'), 'value' => collect(data_get($labelInfo, 'markets', []))->map(fn ($market) => TradeUnitMarketEnum::labels()[$market] ?? $market)->implode(', ') ?: null],
                ['label' => __('Languages'), 'value' => collect(data_get($labelInfo, 'languages', []))->map(fn ($code) => strtoupper($code))->implode(', ') ?: null],
                ['label' => __('Marks on the label'), 'value' => collect(TradeUnitLabelPresenceEnum::labels())->filter(fn ($label, $key) => data_get($labelInfo, $key) === true)->implode(', ') ?: null],
            ], fn (array $line) => filled($line['value']))),
            'pictograms' => $pictograms,
            'barcodes'   => [
                ['level' => 'unit', 'label' => __('Unit EAN-13'), 'value' => $tradeUnit->barcode ?: null, 'note' => null],
                ['level' => 'sko', 'label' => __('SKO (outer packing, Code 128)'), 'value' => $stock?->barcode ?: null, 'note' => $stock ? null : __('No stock created for this SKU yet')],
                ['level' => 'carton', 'label' => __('Carton (Code 128)'), 'value' => $stock?->carton_barcode ?: null, 'note' => $stock ? null : __('No stock created for this SKU yet')],
            ],
            'compliance_route' => ['name' => 'grp.product_developments.show', 'parameters' => ['productDevelopment' => $productDevelopment->slug, 'tab' => 'compliance']],
        ];
    }
}
