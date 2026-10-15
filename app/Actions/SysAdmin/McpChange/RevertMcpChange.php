<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 23 Sep 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\McpChange;

use App\Models\Goods\TradeUnit;
use App\Actions\Goods\TradeUnit\UpdateTradeUnit;
use App\Actions\Catalogue\ProductCategory\RelatedProducts\SyncProductCategoryRelatedProducts;
use App\Actions\Inventory\OrgStock\DiscontinueOrgStocks;
use App\Actions\Inventory\OrgStock\UpdateOrgStock;
use App\Actions\Procurement\OrgSupplierProducts\UpdateOrgSupplierProduct;
use App\Actions\Procurement\PartnerShoppingListItem\DeletePartnerShoppingListItem;
use App\Actions\SupplyChain\SupplierProduct\UpdateSupplierProduct;
use App\Actions\Procurement\PartnerShoppingListItem\UpdatePartnerShoppingListItem;
use App\Actions\Production\Artefact\DetachManufactureTaskFromArtefact;
use App\Actions\Production\Artefact\SetArtefactsRecipe;
use App\Actions\Production\Artefact\SetArtefactState;
use App\Actions\Production\Artefact\UpdateArtefact;
use App\Actions\Production\ManufactureTask\UpdateManufactureTask;
use App\Actions\Production\RawMaterial\UpdateRawMaterial;
use App\Actions\Masters\MasterProductCategory\RelatedChild\RelatedMasterProducts\SyncMasterProductCategoryRelatedMasterAssets;
use App\Actions\GoodsIn\StockDelivery\DeleteStockDeliveryCost;
use App\Actions\GoodsIn\StockDelivery\EvaluateStockDeliveryCosting;
use App\Actions\GoodsIn\StockDelivery\UpdateStockDeliveryCost;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryCostTypeEnum;
use App\Enums\Production\Artefact\ArtefactStateEnum;
use App\Enums\SysAdmin\McpChange\McpChangeTypeEnum;
use App\Models\Catalogue\ProductCategory;
use App\Models\GoodsIn\StockDelivery;
use App\Models\GoodsIn\StockDeliveryCost;
use App\Models\Inventory\OrgStock;
use App\Actions\Masters\MasterAsset\UpdateMasterAssetPrices;
use App\Models\Masters\MasterAsset;
use App\Models\Masters\MasterProductCategory;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\Procurement\PartnerShoppingListItem;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\Production\Artefact;
use App\Models\Production\ManufactureTask;
use App\Models\Production\RawMaterial;
use App\Models\SysAdmin\McpChange;
use App\Models\SysAdmin\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Puts back the state an AI change found, through the same actions the UI uses so caches and
 * cascades run. Refused when anything moved since the change: a later edit by a person is never
 * overwritten silently.
 */
class RevertMcpChange
{
    use AsAction;

    public function handle(McpChange $mcpChange, User $user): McpChange
    {
        if ($mcpChange->type === McpChangeTypeEnum::PLACED_ORDER) {
            throw ValidationException::withMessages(['message' => __('An order already sent cannot be taken back automatically. Cancel it with the partner or supplier.')]);
        }

        if (!$mcpChange->type->canBeReverted()) {
            throw ValidationException::withMessages(['message' => __('What was done for a customer cannot be taken back automatically. Put it right in the chat or on the order.')]);
        }

        if ($mcpChange->reverted_at) {
            throw ValidationException::withMessages(['message' => __('This change was already reverted')]);
        }

        $target  = Arr::get($mcpChange->data, 'target', []);
        $current = GetMcpChangeSnapshot::run($mcpChange->type, $target);

        if ($current != $mcpChange->after) {
            throw ValidationException::withMessages(['message' => __('This was changed again after the AI change, so it cannot be reverted automatically. Look at it and fix it by hand.')]);
        }

        DB::transaction(function () use ($mcpChange, $target, $user) {
            match ($mcpChange->type) {
                McpChangeTypeEnum::RELATED_PRODUCTS => $this->revertRelatedProducts($target, $mcpChange->before),
                McpChangeTypeEnum::ORG_STOCK_STATE  => $this->revertOrgStockStates($mcpChange->before),
                McpChangeTypeEnum::SUPPLIER_PRODUCT_AVAILABILITY => $this->revertSupplierProductAvailability($mcpChange->before),
                McpChangeTypeEnum::PARTNER_SHOPPING_LIST => $this->revertPartnerShoppingList($mcpChange->before, $mcpChange->after),
                McpChangeTypeEnum::PRODUCTION_RECORD => $this->revertProductionRecord($target, $mcpChange->before),
                McpChangeTypeEnum::PRODUCTION_RECIPE => $this->revertProductionRecipes($mcpChange->before),
                McpChangeTypeEnum::PLACED_ORDER, McpChangeTypeEnum::CUSTOMER_SERVICE => null,
                McpChangeTypeEnum::MASTER_PRICES => $this->revertMasterPrices($mcpChange->before),
                McpChangeTypeEnum::STOCK_DELIVERY_COSTS => $this->revertStockDeliveryCosts($target, $mcpChange->before),
                McpChangeTypeEnum::TRADE_UNIT_PROPERTIES => $this->revertTradeUnitProperties($target, $mcpChange->before, $mcpChange->after),
            };

            $mcpChange->update([
                'reverted_at'    => now(),
                'reverted_by_id' => $user->id,
            ]);
        });

        return $mcpChange;
    }

    private function revertStockDeliveryCosts(array $target, array $before): void
    {
        $stockDelivery = StockDelivery::findOrFail($target['stock_delivery_id']);
        if ($stockDelivery->is_costed) {
            throw ValidationException::withMessages(['message' => __('This stock delivery is costed now, so its costs cannot be reverted automatically. An accounting manager changes it with Update costing.')]);
        }

        $costs = $before['costs'];

        $stockDelivery->costs()->where('from_service_invoices', false)->whereNotIn('id', array_keys($costs))->get()->each(function (StockDeliveryCost $cost) use ($stockDelivery) {
            if ($cost->type === StockDeliveryCostTypeEnum::EXTRA) {
                DeleteStockDeliveryCost::make()->action($cost);

                return;
            }
            $cost->delete();
            EvaluateStockDeliveryCosting::run($stockDelivery);
        });

        foreach ($costs as $costId => $fields) {
            $cost = StockDeliveryCost::withTrashed()->find($costId);
            if (!$cost || $cost->from_service_invoices) {
                continue;
            }
            if ($cost->trashed()) {
                $cost->restore();
            }

            UpdateStockDeliveryCost::make()->action($cost, [
                'label'                  => $fields['label'],
                'category'               => $fields['category'],
                'amount'                 => $fields['amount'],
                'currency_id'            => $fields['currency_id'],
                'exchange'               => $fields['exchange'],
                'is_na'                  => $fields['is_na'],
                'received_at'            => $fields['received_at'],
                'is_waiting_for_invoice' => $fields['is_waiting_for_invoice'],
                'invoice_expected_in_days' => $fields['is_waiting_for_invoice'] && $fields['invoice_expected_at']
                    ? max(0, min(365, (int) today()->diffInDays(Carbon::parse($fields['invoice_expected_at']), false)))
                    : null,
            ]);
        }

        EvaluateStockDeliveryCosting::run($stockDelivery);
    }

    /**
     * A barcode once printed on a label can not be taken back from the log: a person moves it in Goods > Barcodes.
     */
    private function revertTradeUnitProperties(array $target, array $before, array $after): void
    {
        if ($before['barcode_id'] != $after['barcode_id']) {
            throw ValidationException::withMessages(['message' => __('This change gave :code a barcode, which cannot be taken back automatically. Change it in Goods > Barcodes.', ['code' => $target['code']])]);
        }

        UpdateTradeUnit::make()->action(TradeUnit::findOrFail($target['trade_unit_id']), Arr::only($before, GetMcpChangeSnapshot::TRADE_UNIT_FIELDS), strict: false);
    }

    private function revertMasterPrices(array $before): void
    {
        foreach ($before['prices'] as $masterAssetId => $prices) {
            $updatePrices                    = UpdateMasterAssetPrices::make();
            $updatePrices->forceAsyncCascade = true;
            $updatePrices->action(MasterAsset::findOrFail($masterAssetId), [
                'master_prices' => collect($prices)
                    ->filter(fn (array $price) => (float) $price['value'] > 0)
                    ->map(fn (array $price) => ['value' => (float) $price['value'], 'independent' => $price['independent']])
                    ->all(),
            ]);
        }
    }

    private function revertRelatedProducts(array $target, array $before): void
    {
        if ($target['level'] === 'master') {
            SyncMasterProductCategoryRelatedMasterAssets::make()->action(MasterProductCategory::findOrFail($target['id']), ['master_asset_ids' => $before['ids']]);

            return;
        }

        SyncProductCategoryRelatedProducts::make()->action(ProductCategory::findOrFail($target['id']), ['product_ids' => $before['ids']]);
    }

    private function revertPartnerShoppingList(array $before, array $after): void
    {
        foreach ($after['lines'] as $stockId => $line) {
            $item     = PartnerShoppingListItem::findOrFail($line['id']);
            $previous = $before['lines'][$stockId] ?? null;

            if ($previous) {
                UpdatePartnerShoppingListItem::make()->action($item, ['quantity' => $previous['quantity'], 'notes' => $previous['notes'], 'break_batch' => true]);
            } else {
                DeletePartnerShoppingListItem::make()->action($item);
            }
        }
    }

    /**
     * A record the assistant created has job orders, recipes and stock hanging off it soon after,
     * so it is never removed automatically.
     */
    private function revertProductionRecord(array $target, array $before): void
    {
        if (!$before) {
            throw ValidationException::withMessages(['message' => __('This change created :code, which cannot be undone automatically. Set it as discontinued or inactive instead.', ['code' => $target['code']])]);
        }

        $fields = Arr::except($before, ['id']);

        if ($target['kind'] === 'artefact') {
            $artefact = Artefact::findOrFail($before['id']);
            if ($artefact->state->value !== $fields['state']) {
                SetArtefactState::make()->action($artefact, ArtefactStateEnum::from($fields['state']));
            }
            UpdateArtefact::make()->action($artefact, Arr::except($fields, ['state']));

            return;
        }

        match ($target['kind']) {
            'raw_material'     => UpdateRawMaterial::make()->action(RawMaterial::findOrFail($before['id']), $fields),
            'manufacture_task' => UpdateManufactureTask::make()->action(ManufactureTask::findOrFail($before['id']), $fields),
        };
    }

    private function revertProductionRecipes(array $before): void
    {
        foreach ($before['artefacts'] as $artefactId => $steps) {
            $artefact = Artefact::findOrFail($artefactId);

            if (!$steps) {
                $artefact->manufactureTasks->each(fn (ManufactureTask $manufactureTask) => DetachManufactureTaskFromArtefact::make()->action($artefact, $manufactureTask));

                continue;
            }

            SetArtefactsRecipe::make()->action($artefact->production, [
                'artefacts' => [$artefact->id],
                'steps'     => $steps,
            ]);
        }
    }

    private function revertSupplierProductAvailability(array $before): void
    {
        foreach ($before['org_supplier_products'] as $orgSupplierProductId => $previous) {
            UpdateOrgSupplierProduct::make()->action(OrgSupplierProduct::findOrFail($orgSupplierProductId), ['is_available' => $previous['is_available']]);
        }

        foreach ($before['supplier_products'] as $supplierProductId => $previous) {
            UpdateSupplierProduct::make()->action(SupplierProduct::findOrFail($supplierProductId), ['is_available' => $previous['is_available']]);
        }
    }

    private function revertOrgStockStates(array $before): void
    {
        foreach ($before['org_stocks'] as $orgStockId => $previous) {
            $orgStock = OrgStock::findOrFail($orgStockId);

            if ($orgStock->state->value !== $previous['state']) {
                $orgStock = UpdateOrgStock::make()->action($orgStock, ['state' => $previous['state']]);
            }

            $data = Arr::except($orgStock->data, [DiscontinueOrgStocks::SCHEDULED_KEY]);
            if ($previous['scheduled']) {
                $data[DiscontinueOrgStocks::SCHEDULED_KEY] = $previous['scheduled'];
            }
            $orgStock->update(['data' => $data]);
        }
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->route('mcpChange')->canBeRevertedBy($request->user());
    }

    public function asController(McpChange $mcpChange, ActionRequest $request): RedirectResponse
    {
        $this->handle($mcpChange, $request->user());

        return back();
    }
}
