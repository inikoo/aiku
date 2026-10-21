<?php

namespace Tests\Feature;

use App\Actions\Goods\Stock\StoreStock;
use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Inventory\LocationOrgStock\StoreLocationOrgStock;
use App\Actions\Inventory\OrgStock\StoreOrgStock;
use App\Actions\Inventory\Warehouse\StoreWarehouse;
use App\Enums\Goods\Stock\StockStateEnum;
use App\Enums\Inventory\LocationStock\LocationStockTypeEnum;
use App\Models\Goods\Stock;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Actions\Dispatching\DeliveryNote\UpdateState\StartHandlingDeliveryNote;
use App\Actions\Dispatching\DeliveryNote\UpdateState\StartPackingDeliveryNote;
use App\Actions\Dispatching\DeliveryNote\UpdateState\UpdateDeliveryNoteStatePacked;
use App\Actions\Dispatching\DeliveryNote\UpdateState\UpdateDeliveryNoteStateToInQueue;
use App\Actions\Dispatching\DeliveryNote\UpdateState\UpdateDeliveryNoteStateToPicked;
use App\Actions\Dispatching\DeliveryNoteItem\StoreDeliveryNoteItem;
use App\Actions\Dispatching\Packing\StorePacking;
use App\Actions\Dispatching\Picking\StorePicking;
use App\Actions\Goods\Stock\UpdateStock;
use App\Actions\Ordering\Order\StoreOrder;
use App\Actions\Ordering\Order\UpdateState\SendOrderToWarehouse;
use App\Actions\Ordering\Order\UpdateState\SubmitOrder;
use App\Actions\Ordering\Transaction\StoreTransaction;
use App\Models\Helpers\Address;
use App\Models\Ordering\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\SysAdmin\Organisation;

/**
 * Takes one order all the way to a packed delivery note, which is the only state in which all four
 * lifecycle timestamps exist.
 */
function packedDeliveryNote($ctx, bool $markWholeNotePacked = false): array
{
    $order = StoreOrder::make()->action($ctx->customer, [
        'reference'        => 'O'.Str::random(8),
        'date'             => date('Y-m-d'),
        'customer_id'      => $ctx->customer->id,
        'delivery_address' => new Address(Address::factory()->definition()),
        'billing_address'  => new Address(Address::factory()->definition()),
    ]);
    StoreTransaction::make()->action($order, $ctx->product->historicAsset, Transaction::factory()->definition());
    $order = SubmitOrder::make()->action($order);

    $deliveryNote = SendOrderToWarehouse::make()->action($order, ['warehouse_id' => $ctx->warehouse->id]);

    $stock    = StoreStock::make()->action($ctx->group, Stock::factory()->definition());
    $stock    = UpdateStock::make()->action($stock, ['state' => StockStateEnum::ACTIVE]);
    $orgStock = StoreOrgStock::make()->action($ctx->organisation, $stock);

    $deliveryNoteItem = StoreDeliveryNoteItem::make()->action($deliveryNote, [
        'delivery_note_id'  => $deliveryNote->id,
        'org_stock_id'      => $orgStock->id,
        'transaction_id'    => $order->transactions()->first()->id,
        'quantity_required' => 10,
    ]);

    $deliveryNote = UpdateDeliveryNoteStateToInQueue::make()->action($deliveryNote, $ctx->user);
    $deliveryNote = StartHandlingDeliveryNote::make()->action($deliveryNote, $ctx->user);

    $location         = StoreLocation::make()->action($ctx->warehouse, Location::factory()->definition());
    $locationOrgStock = StoreLocationOrgStock::make()->action(
        orgStock: $deliveryNoteItem->orgStock,
        location: $location,
        modelData: ['quantity' => 100, 'type' => LocationStockTypeEnum::PICKING, 'fetched_at' => now()],
        strict: false
    );

    StorePicking::make()->action($deliveryNoteItem, $ctx->user, [
        'picker_user_id'        => $ctx->user->id,
        'location_org_stock_id' => $locationOrgStock->id,
        'quantity'              => 10,
    ]);

    $deliveryNote = UpdateDeliveryNoteStateToPicked::run($deliveryNote->refresh());
    $deliveryNote = StartPackingDeliveryNote::make()->action($deliveryNote, $ctx->user);

    if ($markWholeNotePacked) {
        $deliveryNote->update(['parcels' => [['weight' => 1, 'dimensions' => [30, 20, 10]]]]);
        UpdateDeliveryNoteStatePacked::make()->action($deliveryNote, $ctx->user);
    } else {
        StorePacking::make()->action($deliveryNoteItem->refresh(), $ctx->user, []);
    }

    return [$deliveryNote->refresh(), $deliveryNoteItem->refresh()];
}

function clearInstrumentation(int $deliveryNoteId): void
{
    DB::table('pickings')->where('delivery_note_id', $deliveryNoteId)->update(['last_picked_at' => null]);
    DB::table('packings')->where('delivery_note_id', $deliveryNoteId)
        ->update(['queued_at' => null, 'packing_at' => null, 'done_at' => null]);
}

function costFixStockInLocation($group, $organisation, string $code): array
{
    $stock    = StoreStock::make()->action($group, array_merge(Stock::factory()->definition(), ['code' => $code, 'state' => StockStateEnum::ACTIVE]));
    $orgStock = StoreOrgStock::make()->action($organisation, $stock);

    $warehouse = Warehouse::where('organisation_id', $organisation->id)->first() ?? StoreWarehouse::make()->action($organisation, ['code' => 'CFW'.$organisation->id, 'name' => 'CostFix WH']);
    $location  = StoreLocation::make()->action($warehouse, array_merge(Location::factory()->definition(), ['code' => 'CF-'.$code]));
    StoreLocationOrgStock::make()->action($orgStock, $location, ['type' => LocationStockTypeEnum::STORING]);

    return [$orgStock, $location];
}

function costFixStoreDelivery($group, $organisation, int $auroraDeliveryId, array $items): int
{
    $stockDeliveryId = DB::table('stock_deliveries')->insertGetId([
            'group_id'        => $group->id,
            'organisation_id' => $organisation->id,
            'slug'            => 'cf-delivery-'.$auroraDeliveryId,
            'parent_type'     => 'OrgSupplier',
            'parent_id'       => 1,
            'parent_code'     => 'CF-SUP',
            'parent_name'     => 'CostFix Supplier',
            'reference'       => 'CF'.$auroraDeliveryId,
            'state'           => 'placed',
            'date'            => now(),
            'currency_id'     => $organisation->currency_id,
            'cost_data'       => '{}',
            'data'            => '{}',
            'source_id'       => $organisation->id.':'.$auroraDeliveryId,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    foreach ($items as $item) {
        DB::table('stock_delivery_items')->insert([
            'group_id'          => $group->id,
            'organisation_id'   => $organisation->id,
            'stock_delivery_id' => $stockDeliveryId,
            'org_stock_id'      => $item['org_stock_id'],
            'state'             => 'placed',
            'data'              => '{}',
            'unit_quantity'     => $item['unit_quantity'],
            'unit_quantity_placed' => $item['unit_quantity_placed'] ?? $item['unit_quantity'],
            'net_amount'        => $item['net_amount'],
            'org_net_amount'    => $item['org_net_amount'] ?? $item['net_amount'],
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    return $stockDeliveryId;
}

function ensureFirstWarehouseHasCountry(Organisation $organisation): void
{
    $warehouse = $organisation->warehouses()->oldest('id')->first();
    if ($warehouse && !$warehouse->address?->country_code) {
        $warehouse->update(['address_id' => Address::factory()->create(['group_id' => $organisation->group_id, 'address_line_1' => 'Default warehouse'])->id]);
    }
}

function ensureWarehouseLayoutAndOrgStockFamily(\App\Models\SysAdmin\Organisation $organisation): void
{
    $warehouse     = createWarehouse();
    $warehouseArea = \App\Models\Inventory\WarehouseArea::where('warehouse_id', $warehouse->id)->oldest('id')->first()
        ?? \App\Actions\Inventory\WarehouseArea\StoreWarehouseArea::make()->action($warehouse, \App\Models\Inventory\WarehouseArea::factory()->definition());

    if (!Location::where('warehouse_id', $warehouse->id)->exists()) {
        StoreLocation::make()->action($warehouseArea, Location::factory()->definition());
    }

    if (!\App\Models\Inventory\OrgStockFamily::where('organisation_id', $organisation->id)->exists()) {
        $stockFamily = \App\Actions\Goods\StockFamily\StoreStockFamily::make()->action($organisation->group, \App\Models\Goods\StockFamily::factory()->definition());
        StoreStock::make()->action($stockFamily, array_merge(Stock::factory()->definition(), ['state' => StockStateEnum::ACTIVE]));
        \App\Actions\Inventory\OrgStockFamily\StoreOrgStockFamily::make()->action($organisation, $stockFamily, []);
    }
}

/**
 * @return array{0: \App\Models\Inventory\OrgStock, 1: array<int, int>}
 */
function runningValuesStockWithMovements($group, $organisation, string $code, int $repeats = 1): array
{
    [$orgStock, $location] = costFixStockInLocation($group, $organisation, $code);
    $secondLocation        = StoreLocation::make()->action($location->warehouse, array_merge(Location::factory()->definition(), ['code' => 'CF-'.$code.'2']));
    StoreLocationOrgStock::make()->action($orgStock, $secondLocation, ['type' => LocationStockTypeEnum::STORING]);
    $orgStock->update(['current_supplier_sku_cost' => 1.75]);

    $movements = [
        [$location, \App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum::FOUND, 5, null, 0, 40],
        [$location, \App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum::PURCHASE, 12, 1.37, 0, 35],
        [$location, \App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum::PICKED, -4, null, 0, 25],
        [$location, \App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum::PURCHASE, 9, null, 26.1, 18],
        [$secondLocation, \App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum::FOUND, 3, null, 0, 16],
        [$location, \App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum::PICKED, -30, null, 0, 14],
        [$location, \App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum::FOUND, 2, null, 0, 13],
        [$secondLocation, \App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum::FOUND, 1, null, 0, 13],
        [$location, \App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum::PURCHASE, 7, 3.11, 0, 10],
        [$location, \App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum::AUDIT, 6, null, 0, 8],
        [$secondLocation, \App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum::PICKED, -2, null, 0, 6],
        [$location, \App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum::PURCHASE, 4, 0, 0, 3],
        [$location, \App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum::PICKED, -1, null, 0, 1],
    ];

    $movementIds = [];
    for ($repeat = 0; $repeat < $repeats; $repeat++) {
        foreach ($movements as [$movementLocation, $type, $quantity, $costPerSku, $orgAmount, $daysAgo]) {
            $movement = \App\Actions\Inventory\OrgStockMovement\StoreOrgStockMovement::make()->action($orgStock, $movementLocation, ['type' => $type->value, 'quantity' => $quantity]);
            $movement->update([
                'cost_per_sku' => $costPerSku,
                'org_amount'   => $orgAmount,
                'date'         => now()->subDays($daysAgo)->addMinutes($repeat),
                'source_id'    => $type === \App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum::AUDIT ? $organisation->id.':99'.$movement->id : null,
            ]);
            $movementIds[] = $movement->id;
        }
    }

    return [$orgStock, $movementIds];
}
