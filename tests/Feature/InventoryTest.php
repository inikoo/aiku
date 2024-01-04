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
use App\Actions\Inventory\Stock\StoreStock;
use App\Actions\Inventory\Stock\AttachStockToLocation;
use App\Actions\Inventory\Stock\SyncStockTradeUnits;
use App\Actions\Inventory\StockFamily\StoreStockFamily;
use App\Actions\Inventory\Warehouse\StoreWarehouse;
use App\Actions\Inventory\Warehouse\UpdateWarehouse;
use App\Actions\Inventory\WarehouseArea\StoreWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UpdateWarehouseArea;
use App\Enums\Inventory\Stock\LostAndFoundStockStateEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Inventory\Location;
use App\Models\Inventory\LocationStock;
use App\Models\Inventory\LostAndFoundStock;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Validation\ValidationException;

beforeAll(function () {
    loadDB('test_base_database.dump');
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->group        = group();
    $this->guest        = createAdminGuest($this->group);
});

test('create warehouse', function () {
    $warehouse = StoreWarehouse::make()->action(
        $this->organisation,
        [
            'code' => 'ts12',
            'name' => 'testName',
        ]
    );

    expect($warehouse)->toBeInstanceOf(Warehouse::class)
        ->and($this->organisation->inventoryStats->number_warehouses)->toBe(1)
        ->and($this->guest->user->authorisedWarehouses()->where('organisation_id', $this->organisation->id)->count())->toBe(1)
        ->and($this->guest->user->number_authorised_warehouses)->toBe(1)
        ->and($this->guest->user->hasPermissionTo("warehouses.$warehouse->slug"))->toBeTrue();


    return $warehouse;
});

test('warehouse cannot be created with same code', function () {
    StoreWarehouse::make()->action(
        $this->organisation,
        [
            'code' => 'ts12',
            'name' => 'testName',
        ]
    );
})->depends('create warehouse')->throws(ValidationException::class);

test('warehouse cannot be created with same code case is sensitive', function () {
    StoreWarehouse::make()->action(
        $this->organisation,
        [
            'code' => 'TS12',
            'name' => 'testName',
        ]
    );
})->depends('create warehouse')->throws(ValidationException::class);

test('update warehouse', function ($warehouse) {
    $warehouse = UpdateWarehouse::make()->action($warehouse, ['name' => 'Pika Ltd']);
    expect($warehouse->name)->toBe('Pika Ltd');
})->depends('create warehouse');

test('create warehouse by command', function () {
    $this->artisan('warehouse:create', [
        'organisation' => $this->organisation->slug,
        'code'         => 'AA',
        'name'         => 'testName A',
    ])->assertExitCode(0);

    $warehouse = Warehouse::where('code', 'AA')->first();

    $organisation = $this->organisation;
    $organisation->refresh();


    expect($organisation->inventoryStats->number_warehouses)->toBe(2)
        ->and($warehouse->roles()->count())->toBe(2);
});

test('seed warehouse permissions', function () {
    setPermissionsTeamId($this->group->id);
    $this->artisan('warehouse:seed-permissions')->assertExitCode(0);
    $warehouse = Warehouse::where('code', 'AA')->first();
    expect($warehouse->roles()->count())->toBe(2);
});


test('create warehouse area', function ($warehouse) {
    $warehouseArea = StoreWarehouseArea::make()->action($warehouse, WarehouseArea::factory()->definition());
    expect($warehouseArea)->toBeInstanceOf($warehouseArea::class)
        ->and($this->organisation->inventoryStats->number_warehouse_areas)->toBe(1);

    return $warehouseArea;
})->depends('create warehouse');

test('update warehouse area', function ($warehouseArea) {
    $warehouseArea = UpdateWarehouseArea::make()->action($warehouseArea, ['name' => 'Area 01']);
    expect($warehouseArea->name)->toBe('Area 01');
})->depends('create warehouse area');

test('create location in warehouse', function ($warehouse) {
    $location = StoreLocation::make()->action($warehouse, Location::factory()->definition());
    $warehouse->refresh();
    expect($location)->toBeInstanceOf(Location::class)
        ->and($this->organisation->inventoryStats->number_locations)->toBe(1)
        ->and($this->organisation->inventoryStats->number_locations_state_operational)->toBe(1)
        ->and($this->organisation->inventoryStats->number_locations_state_broken)->toBe(0)
        ->and($warehouse->stats->number_locations)->toBe(1)
        ->and($warehouse->stats->number_locations_state_operational)->toBe(1)
        ->and($warehouse->stats->number_locations_state_broken)->toBe(0);
})->depends('create warehouse');

test('create location in warehouse area', function ($warehouseArea) {
    $location = StoreLocation::make()->action($warehouseArea, Location::factory()->definition());
    $warehouseArea->refresh();
    $warehouse = $warehouseArea->warehouse;

    expect($location)->toBeInstanceOf(Location::class)
        ->and($this->organisation->inventoryStats->number_locations)->toBe(2)
        ->and($this->organisation->inventoryStats->number_locations_state_operational)->toBe(2)
        ->and($this->organisation->inventoryStats->number_locations_state_broken)->toBe(0)
        ->and($warehouse->stats->number_locations)->toBe(2)
        ->and($warehouse->stats->number_locations_state_operational)->toBe(2)
        ->and($warehouse->stats->number_locations_state_broken)->toBe(0)
        ->and($warehouseArea->stats->number_locations)->toBe(1)
        ->and($warehouseArea->stats->number_locations_state_operational)->toBe(1)
        ->and($warehouseArea->stats->number_locations_state_broken)->toBe(0);

    return $location;
})->depends('create warehouse area');


test('create stock families', function () {
    $arrayData = [
        'code' => 'ABC',
        'name' => 'ABC Stocks'
    ];

    $stockFamily = StoreStockFamily::make()->action($this->group, $arrayData);

    expect($stockFamily->code)->toBe($arrayData['code']);

    return $stockFamily;
});

test('create stock', function () {
    $tradeUnit = StoreTradeUnit::make()->action(TradeUnit::factory()->definition());

    $stock = StoreStock::make()->action($this->group, Stock::factory()->definition());

    SyncStockTradeUnits::run($stock, [
        $tradeUnit->id => [
            'quantity' => 2
        ]
    ]);

    expect($stock)->toBeInstanceOf(Stock::class)
        ->and($this->group->inventoryStats->number_stocks)->toBe(1);

    return $stock->fresh();
});


test('create another stock', function () {
    $tradeUnit = StoreTradeUnit::make()->action(TradeUnit::factory()->definition());
    $stock     = StoreStock::make()->action($this->group, Stock::factory()->definition());

    SyncStockTradeUnits::run($stock, [
        $tradeUnit->id => [
            'quantity' => 1
        ]
    ]);
    expect($stock)->toBeInstanceOf(Stock::class)
        ->and($this->group->inventoryStats->number_stocks)->toBe(2);

    return $stock->fresh();
});

test('attach stock to location', function ($location) {
    $stocks = Stock::all();
    expect($stocks->count())->toBe(2);
    foreach ($stocks as $stock) {
        $location = AttachStockToLocation::run($location, $stock);
    }
    expect($location->stocks()->count())->toBe(2)
        ->and($location->stats->number_stock_slots)->toBe(2);
})->depends('create location in warehouse area');


test('detach stock from location', function ($location, $stock) {
    DetachStockFromLocation::run($location, $stock);
    $location->refresh();
    expect($location->stats->number_stock_slots)->toBe(1);
})->depends('create location in warehouse area', 'create stock');

test('move stock location', function () {
    $currentLocation = LocationStock::first();
    $targetLocation  = LocationStock::latest()->first();

    $stock = MoveStockLocation::make()->action($currentLocation, $targetLocation, [
        'quantity' => 1
    ]);

    expect($stock->quantity)->toBeNumeric(1);
})->depends('detach stock from location');

test('update location', function ($location) {
    $location = UpdateLocation::make()->action($location, ['code' => 'AE-3']);
    expect($location->code)->toBe('AE-3');
})->depends('create location in warehouse area');

test('audit stock in location', function ($location) {
    $location = AuditLocation::run($location);
    expect($location->audited_at)->not->toBeNull();
})->depends('create location in warehouse area');

test('add found stock', function ($location) {
    $lostAndFound = AddLostAndFoundStock::make()->action(
        $location,
        array_merge(LostAndFoundStock::factory()->definition(), [
            'type' => LostAndFoundStockStateEnum::FOUND->value
        ])
    );

    expect($lostAndFound->type)->toBe(LostAndFoundStockStateEnum::FOUND->value);

    return $lostAndFound;
})->depends('create location in warehouse area');

test('add lost stock', function ($location) {
    $lostAndFound = AddLostAndFoundStock::make()->action(
        $location,
        array_merge(LostAndFoundStock::factory()->definition(), [
            'type' => LostAndFoundStockStateEnum::LOST->value
        ])
    );

    expect($lostAndFound->type)->toBe(LostAndFoundStockStateEnum::LOST->value);

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
