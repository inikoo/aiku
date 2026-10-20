<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 11:20
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\GoodsIn\StockDeliveryPackingCheck;

use App\Actions\OrgAction;
use App\Actions\Tasks\GetAikuAssistant;
use App\Actions\Tasks\StoreStaffTask;
use App\Actions\Traits\Authorisations\WithGoodsInBookInAuthorisation;
use App\Enums\GoodsIn\StockDeliveryItem\StockDeliveryItemStateEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\GoodsIn\StockDeliveryItem;
use App\Models\GoodsIn\StockDeliveryPackingCheck;
use App\Models\SupplyChain\SupplierProductPackingSpec;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;

/**
 * When a delivery is received, each of its supplier products with a packing spec gets a check: one carton in twenty
 * weighed gross, and on the first delivery of a new spec version each packing material weighed. The materials to
 * weigh go to the warehouse as one staff task for the delivery.
 */
class CreateStockDeliveryPackingChecks extends OrgAction
{
    use WithGoodsInBookInAuthorisation;

    /**
     * @return Collection<int, StockDeliveryPackingCheck>
     */
    public function handle(StockDelivery $stockDelivery): Collection
    {
        $date  = $stockDelivery->received_at ?? now();
        $items = $stockDelivery->items()
            ->whereNotIn('state', [StockDeliveryItemStateEnum::CANCELLED, StockDeliveryItemStateEnum::NOT_RECEIVED])
            ->whereNotNull('supplier_product_id')
            ->with('supplierProduct')
            ->get();

        $checks = $items->map(function (StockDeliveryItem $item) use ($stockDelivery, $date) {
            $spec = SupplierProductPackingSpec::effectiveFor($item->supplier_product_id, $date);
            if (!$spec) {
                return null;
            }

            $cartons = $spec->cartonsOf($item);

            return StockDeliveryPackingCheck::firstOrCreate(
                ['stock_delivery_item_id' => $item->id, 'packing_spec_id' => $spec->id],
                [
                    'group_id'          => $stockDelivery->group_id,
                    'organisation_id'   => $stockDelivery->organisation_id,
                    'stock_delivery_id' => $stockDelivery->id,
                    'weigh_materials'   => $this->isFirstDeliveryOf($spec),
                    'cartons_to_check'  => max(1, (int)ceil($cartons / StockDeliveryPackingCheck::CARTONS_PER_CHECK)),
                ]
            );
        })->filter()->values();

        $toWeigh = $checks->filter(fn (StockDeliveryPackingCheck $check) => $check->weigh_materials && !$check->staff_task_id);
        if ($toWeigh->isNotEmpty()) {
            $this->askWarehouseToWeigh($stockDelivery, $toWeigh);
        }

        return $checks;
    }

    private function isFirstDeliveryOf(SupplierProductPackingSpec $spec): bool
    {
        return !$spec->materials_weighed
            && !StockDeliveryPackingCheck::where('packing_spec_id', $spec->id)->where('weigh_materials', true)->exists();
    }

    /**
     * @param Collection<int, StockDeliveryPackingCheck> $checks
     */
    private function askWarehouseToWeigh(StockDelivery $stockDelivery, Collection $checks): void
    {
        $checks->each(fn (StockDeliveryPackingCheck $check) => $check->loadMissing(['stockDeliveryItem.supplierProduct', 'packingSpec']));

        $task = StoreStaffTask::run(GetAikuAssistant::run($stockDelivery->group_id), [
            'subject'     => __('Weigh the packing materials of :reference', ['reference' => $stockDelivery->reference]),
            'description' => __('These products arrived with a new packing spec. Open one carton of each, weigh every packing material on its own and enter the grams in the Packing checks tab of the delivery. Also weigh the cartons it asks for.'),
            'department'  => 'warehouse',
            'subtasks'    => $checks->map(fn (StockDeliveryPackingCheck $check) => [
                'title'  => $check->stockDeliveryItem->supplierProduct->code.' · '.$check->packingSpec->packingLabel(),
                'status' => 'todo',
            ])->values()->all(),
            'data'        => ['organisation_id' => $stockDelivery->organisation_id, 'stock_delivery_id' => $stockDelivery->id, 'kind' => 'packing_spec_weighing'],
        ]);

        StockDeliveryPackingCheck::whereIn('id', $checks->pluck('id'))->update(['staff_task_id' => $task->id]);
    }

    public function asController(StockDelivery $stockDelivery, ActionRequest $request): RedirectResponse
    {
        $this->initialisation($stockDelivery->organisation, $request);
        $this->handle($stockDelivery);

        return redirect()->back();
    }
}
