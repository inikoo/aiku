<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 11:50
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\GoodsIn\StockDeliveryPackingCheck\UI;

use App\Enums\SupplyChain\PackingSpec\PackingCheckResultEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecFateEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecLevelEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecStatusEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\GoodsIn\StockDeliveryPackingCheck;
use App\Models\SupplyChain\PackingSpecLine;
use App\Models\SupplyChain\SupplierProductPackingSpec;
use App\Models\Tasks\StaffTask;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * The Packing checks tab of a received delivery: per supplier product with a packing spec, the cartons to weigh, the
 * materials to weigh on a new spec version, and what was found.
 */
class GetStockDeliveryPackingChecks
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(StockDelivery $stockDelivery, bool $canCheck): array
    {
        $checks = StockDeliveryPackingCheck::where('stock_delivery_id', $stockDelivery->id)
            ->with(['stockDeliveryItem.supplierProduct', 'stockDeliveryItem.historicSupplierProduct', 'packingSpec.lines.packagingComponent', 'packingSpec.supplierProduct.tradeUnits', 'checkedBy'])
            ->get()
            ->sortBy(fn (StockDeliveryPackingCheck $check) => $check->stockDeliveryItem->supplierProduct?->code)
            ->values();

        $tasks = StaffTask::whereIn('id', $checks->pluck('staff_task_id')->filter()->unique())->get(['id', 'reference', 'status'])->keyBy('id');

        $itemsWithoutSpec = $stockDelivery->items()
            ->whereNotNull('supplier_product_id')
            ->whereNotIn('id', $checks->pluck('stock_delivery_item_id'))
            ->count();

        return [
            'tolerance_pct'      => SupplierProductPackingSpec::WEIGHT_TOLERANCE_PCT,
            'items_without_spec' => $itemsWithoutSpec,
            'fates'              => PackingSpecFateEnum::labels(),
            'levels'             => PackingSpecLevelEnum::labels(),
            'create_route'       => $canCheck ? ['name' => 'grp.models.stock-delivery.packing_checks.store', 'parameters' => [$stockDelivery->id]] : null,
            'checks'             => $checks->map(function (StockDeliveryPackingCheck $check) use ($canCheck, $tasks) {
                $spec = $check->packingSpec;

                return [
                    'id'               => $check->id,
                    'code'             => $check->stockDeliveryItem->supplierProduct?->code,
                    'name'             => $check->stockDeliveryItem->supplierProduct?->name,
                    'spec_version'     => $spec->version,
                    'spec_status'      => $spec->status->value,
                    'spec_status_label' => PackingSpecStatusEnum::labels()[$spec->status->value],
                    'cartons'          => round($spec->cartonsOf($check->stockDeliveryItem), 1),
                    'cartons_to_check' => $check->cartons_to_check,
                    'weigh_materials'  => $check->weigh_materials,
                    'expected_gross_g' => $spec->expectedCartonGrossWeightG(),
                    'carton_weights_g' => $check->carton_weights_g,
                    'deviation_pct'    => $check->deviation_pct === null ? null : (float)$check->deviation_pct,
                    'result'           => $check->result->value,
                    'result_label'     => PackingCheckResultEnum::labels()[$check->result->value],
                    'checked_by'       => $check->checkedBy?->contact_name ?: $check->checkedBy?->username,
                    'checked_at'       => $check->checked_at?->toIso8601String(),
                    'task'             => $check->staff_task_id && $tasks->has($check->staff_task_id) ? $tasks->get($check->staff_task_id)->reference : null,
                    'lines'            => $spec->lines->map(fn (PackingSpecLine $line) => [
                        'id'                     => $line->id,
                        'label'                  => $line->description ?: $line->packagingComponent->dropdownLabel(),
                        'level'                  => $line->level->value,
                        'quantity'               => (float)$line->quantity,
                        'declared_weight_g_each' => $line->declared_weight_g_each === null ? null : (float)$line->declared_weight_g_each,
                        'weight_g_each'          => $line->weight_g_each === null ? null : (float)$line->weight_g_each,
                        'weight_is_measured'     => $line->weight_is_measured,
                        'fate_at_our_warehouse'  => $line->fate_at_our_warehouse->value,
                    ])->values(),
                    'record_route' => $canCheck ? ['name' => 'grp.models.stock_delivery_packing_check.record', 'parameters' => [$check->id]] : null,
                ];
            })->values(),
        ];
    }
}
