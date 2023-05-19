<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Inventory\Location\AuditLocation;
use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Inventory\Location\UpdateLocation;
use App\Actions\Inventory\Stock\AddLostAndFoundStock;
use App\Actions\Inventory\Stock\DetachStockFromLocation;
use App\Actions\Inventory\Stock\MoveStockLocation;
use App\Actions\Inventory\Stock\RemoveLostAndFoundStock;
use App\Actions\Inventory\Stock\RemoveStockTradeUnits;
use App\Actions\Inventory\Stock\StoreStock;
use App\Actions\Inventory\Stock\AttachStockToLocation;
use App\Actions\Inventory\Stock\SyncStockTradeUnits;
use App\Actions\Inventory\StockFamily\StoreStockFamily;
use App\Actions\Inventory\Warehouse\StoreWarehouse;
use App\Actions\Inventory\Warehouse\UpdateWarehouse;
use App\Actions\Inventory\WarehouseArea\StoreWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UpdateWarehouseArea;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Enums\Inventory\Stock\LostAndFoundStockStateEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Inventory\Location;
use App\Models\Inventory\LocationStock;
use App\Models\Inventory\LostAndFoundStock;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;
use Illuminate\Validation\ValidationException;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $tenant = Tenant::first();
    if (!$tenant) {
        $group  = StoreGroup::make()->asAction(Group::factory()->definition());
        $tenant = StoreTenant::make()->action($group, Tenant::factory()->definition());
    }
    $tenant->makeCurrent();
});

test('create warehouse', function () {
    $warehouse = StoreWarehouse::make()->action([
        'code' => 'ts12',
        'name'  => 'testName',
    ]);
    expect($warehouse)->toBeInstanceOf(Warehouse::class);
    return $warehouse;
});

test('warehouse cannot be created with same code', function () {
    $warehouse = StoreWarehouse::make()->action([
        'code' => 'ts12',
        'name'  => 'testName',
    ]);

})->depends('create warehouse')->throws(ValidationException::class);

test('warehouse cannot be created with same code case is sensitive', function () {
    $warehouse = StoreWarehouse::make()->action([
        'code' => 'TS12',
        'name'  => 'testName',
    ]);

})->depends('create warehouse')->throws(ValidationException::class);

test('update warehouse', function ($warehouse) {
    $warehouse = UpdateWarehouse::make()->action($warehouse, ['name' => 'Pika Ltd']);
    expect($warehouse->name)->toBe('Pika Ltd');
})->depends('create warehouse');

test('create warehouse area', function ($warehouse) {
    $warehouseArea = StoreWarehouseArea::make()->action($warehouse, WarehouseArea::factory()->definition());
    $this->assertModelExists($warehouseArea);
    return $warehouseArea;
})->depends('create warehouse');

test('update warehouse area', function ($warehouseArea) {
    $warehouseArea = UpdateWarehouseArea::make()->action($warehouseArea, ['name' => 'Pika Ltd']);
    expect($warehouseArea->name)->toBe('Pika Ltd');
})->depends('create warehouse area');

test('create location in warehouse', function ($warehouse) {
    $location = StoreLocation::make()->action($warehouse, Location::factory()->definition());
    $this->assertModelExists($location);
})->depends('create warehouse');

test('create location in warehouse area', function ($warehouseArea) {
    $location = StoreLocation::make()->action($warehouseArea, Location::factory()->definition());
    $this->assertModelExists($location);
    return $location;
})->depends('create warehouse area');

test('create stock families', function () {
    $stockFamily = StoreStockFamily::make()->action(StockFamily::factory()->definition());
    $this->assertModelExists($stockFamily);

    return $stockFamily;
});

test('create stock', function () {
    $stock = StoreStock::make()->action(app('currentTenant'), Stock::factory()->definition());
    $this->assertModelExists($stock);

    return $stock->fresh();
});

test('create another stock', function () {
    $stock = StoreStock::make()->action(app('currentTenant'), Stock::factory()->definition());
    $this->assertModelExists($stock);

    return $stock->fresh();
});

test('attach stock to location', function ($location) {
    $stocks   = Stock::all();
    $location = AttachStockToLocation::run($location, $stocks->pluck('id'));
    $this->assertModelExists($location);
})->depends('create location in warehouse area');

test('create trade unit', function () {
    $tradeUnit = StoreTradeUnit::make()->action(TradeUnit::factory()->definition());
    $this->assertModelExists($tradeUnit);

    return $tradeUnit->fresh();
});

test('add trade unit to stock', function ($stock, $tradeUnit) {
    $stock = SyncStockTradeUnits::run($stock, [$tradeUnit->id]);
    $this->assertModelExists($stock);
})->depends('create stock', 'create trade unit');

test('remove trade unit from stock', function ($stock, $tradeUnit) {
    $stock = RemoveStockTradeUnits::run($stock, [$tradeUnit->id]);
    $this->assertModelExists($stock);
})->depends('create stock', 'create trade unit');

test('detach stock from location', function ($location, $stock) {
    $stock = DetachStockFromLocation::run($location, $stock);
    $this->assertModelExists($stock);
})->depends('create location in warehouse area', 'create stock');

test('move stock location', function () {
    $currentLocation = LocationStock::first();
    $targetLocation  = LocationStock::latest()->first();

    $stock = MoveStockLocation::make()->action($currentLocation, $targetLocation, [
        'quantity' => 1
    ]);
    $this->assertModelExists($stock);
});

test('update location', function ($location) {
    $location = UpdateLocation::make()->action($location, ['code' => 'AE-3']);
    expect($location->code)->toBe('AE-3');

})->depends('create location in warehouse area');

test('audit stock in location', function ($location) {
    $location = AuditLocation::run($location);
    expect($location->audited_at)->not->toBeNull();
})->depends('create location in warehouse area');

test('add found stock', function ($location) {
    $lostAndFound = AddLostAndFoundStock::make()->action($location, array_merge(LostAndFoundStock::factory()->definition(), [
        'type' => LostAndFoundStockStateEnum::FOUND->value
    ]));
    $this->assertModelExists($lostAndFound);

    return $lostAndFound;
})->depends('create location in warehouse area');

test('add lost stock', function ($location) {
    $lostAndFound = AddLostAndFoundStock::make()->action($location, array_merge(LostAndFoundStock::factory()->definition(), [
        'type' => LostAndFoundStockStateEnum::LOST->value
    ]));
    $this->assertModelExists($lostAndFound);

    return $lostAndFound;
})->depends('create location in warehouse area');

test('remove lost stock', function ($lostAndFoundStock) {
    $lostAndFound = RemoveLostAndFoundStock::make()->action($lostAndFoundStock, 2);
    expect($lostAndFound->quantity)->toBe(2.0);
})->depends('add lost stock');

test('remove found stock', function ($lostAndFoundStock) {
    $lostAndFound = RemoveLostAndFoundStock::make()->action($lostAndFoundStock, 2);
    expect($lostAndFound->quantity)->toBe(2.0);
})->depends('add found stock');
