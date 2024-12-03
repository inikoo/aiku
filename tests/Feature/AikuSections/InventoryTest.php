<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 02 Dec 2024 15:01:28 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Feature;

use App\Actions\Goods\Stock\StoreStock;
use App\Actions\Goods\StockFamily\StoreStockFamily;
use App\Actions\Helpers\Tag\StoreTag;
use App\Actions\Inventory\Location\DeleteLocation;
use App\Actions\Inventory\Location\HydrateLocation;
use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Inventory\Location\Tags\SyncTagsLocation;
use App\Actions\Inventory\Location\UpdateLocation;
use App\Actions\Inventory\LocationOrgStock\AuditLocationOrgStock;
use App\Actions\Inventory\LocationOrgStock\DeleteLocationOrgStock;
use App\Actions\Inventory\LocationOrgStock\MoveOrgStockToOtherLocation;
use App\Actions\Inventory\LocationOrgStock\StoreLocationOrgStock;
use App\Actions\Inventory\LocationOrgStock\UpdateLocationOrgStock;
use App\Actions\Inventory\OrgStock\AddLostAndFoundOrgStock;
use App\Actions\Inventory\OrgStock\RemoveLostAndFoundStock;
use App\Actions\Inventory\OrgStock\StoreOrgStock;
use App\Actions\Inventory\OrgStock\UpdateOrgStock;
use App\Actions\Inventory\OrgStockFamily\StoreOrgStockFamily;
use App\Actions\Inventory\Warehouse\HydrateWarehouse;
use App\Actions\Inventory\Warehouse\StoreWarehouse;
use App\Actions\Inventory\Warehouse\UpdateWarehouse;
use App\Actions\Inventory\WarehouseArea\HydrateWarehouseArea;
use App\Actions\Inventory\WarehouseArea\StoreWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UpdateWarehouseArea;
use App\Enums\Inventory\LocationStock\LocationStockTypeEnum;
use App\Enums\Inventory\OrgStock\LostAndFoundOrgStockStateEnum;
use App\Enums\Inventory\OrgStockFamily\OrgStockFamilyStateEnum;
use App\Enums\SupplyChain\Stock\StockStateEnum;
use App\Models\Helpers\Tag;
use App\Models\Inventory\Location;
use App\Models\Inventory\LocationOrgStock;
use App\Models\Inventory\LostAndFoundStock;
use App\Models\Inventory\OrgStock;
use App\Models\Inventory\OrgStockFamily;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use Illuminate\Validation\ValidationException;

beforeAll(function () {
    loadDB();
});

beforeEach(
    /**
     * @throws \Throwable
     */
    function () {
        $this->organisation = createOrganisation();
        $this->group        = group();
        $this->guest        = createAdminGuest($this->group);
    }
);

test('create warehouse', function () {
    $warehouse = StoreWarehouse::make()->action(
        $this->organisation,
        [
            'code' => 'ts12',
            'name' => 'testName',
        ]
    );

    $user = $this->guest->getUser();
    $user->refresh();

    expect($warehouse)->toBeInstanceOf(Warehouse::class)
        ->and($this->organisation->inventoryStats->number_warehouses)->toBe(1)
        ->and($this->organisation->inventoryStats->number_warehouses_state_in_process)->toBe(1)
        ->and($this->organisation->inventoryStats->number_warehouses_state_open)->toBe(0)
        ->and($this->organisation->inventoryStats->number_warehouses_state_closing_down)->toBe(0)
        ->and($this->organisation->inventoryStats->number_warehouses_state_closed)->toBe(0)
        ->and($this->organisation->group->inventoryStats->number_warehouses)->toBe(1)
        ->and($this->organisation->group->inventoryStats->number_warehouses_state_in_process)->toBe(1)
        ->and($this->organisation->group->inventoryStats->number_warehouses_state_open)->toBe(0)
        ->and($user->authorisedWarehouses()->where('organisation_id', $this->organisation->id)->count())->toBe(1)
        ->and($user->number_authorised_warehouses)->toBe(1);


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
        ->and($organisation->group->inventoryStats->number_warehouses)->toBe(2)
        ->and($warehouse->roles()->count())->toBe(8);
});

test('seed warehouse permissions', function () {
    setPermissionsTeamId($this->group->id);
    $this->artisan('warehouse:seed-permissions')->assertExitCode(0);
    $warehouse = Warehouse::where('code', 'AA')->first();
    expect($warehouse->roles()->count())->toBe(8);
});


test('create warehouse area', function ($warehouse) {
    $warehouseArea = StoreWarehouseArea::make()->action($warehouse, WarehouseArea::factory()->definition());
    expect($warehouseArea)->toBeInstanceOf($warehouseArea::class)
        ->and($this->organisation->inventoryStats->number_warehouse_areas)->toBe(1)
        ->and($this->organisation->group->inventoryStats->number_warehouse_areas)->toBe(1);

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
        ->and($this->organisation->inventoryStats->number_locations_status_operational)->toBe(1)
        ->and($this->organisation->inventoryStats->number_locations_status_broken)->toBe(0)
        ->and($this->organisation->group->inventoryStats->number_locations)->toBe(1)
        ->and($this->organisation->group->inventoryStats->number_locations_status_operational)->toBe(1)
        ->and($this->organisation->group->inventoryStats->number_locations_status_broken)->toBe(0)
        ->and($warehouse->stats->number_locations)->toBe(1)
        ->and($warehouse->stats->number_locations_status_operational)->toBe(1)
        ->and($warehouse->stats->number_locations_status_broken)->toBe(0);

    return $location;
})->depends('create warehouse');

test('create other location in warehouse', function ($warehouse) {

    StoreLocation::make()->action(
        $warehouse,
        [
            'code'       => 'AA',
            'max_weight' => 1000,
        ]
    );

    $warehouse->refresh();
    expect($warehouse->stats->number_locations)->toBe(2)
        ->and($warehouse->stats->number_locations_status_operational)->toBe(2)
        ->and($warehouse->stats->number_locations_status_broken)->toBe(0);
})->depends('create warehouse');

test('create location in warehouse area', function ($warehouseArea) {
    $location = StoreLocation::make()->action($warehouseArea, Location::factory()->definition());
    $warehouseArea->refresh();
    $warehouse = $warehouseArea->warehouse;

    expect($location)->toBeInstanceOf(Location::class)
        ->and($this->organisation->inventoryStats->number_locations)->toBe(3)
        ->and($this->organisation->inventoryStats->number_locations_status_operational)->toBe(3)
        ->and($this->organisation->inventoryStats->number_locations_status_broken)->toBe(0)
        ->and($warehouse->stats->number_locations)->toBe(3)
        ->and($warehouse->stats->number_locations_status_operational)->toBe(3)
        ->and($warehouse->stats->number_locations_status_broken)->toBe(0)
        ->and($warehouseArea->stats->number_locations)->toBe(1)
        ->and($warehouseArea->stats->number_locations_status_operational)->toBe(1)
        ->and($warehouseArea->stats->number_locations_status_broken)->toBe(0);

    return $location;
})->depends('create warehouse area');

test('delete location', function (Location $location) {
    $location = DeleteLocation::make()->action($location);

    expect(Location::find($location->id))->toBeNull();

    return $location;
})->depends('create location in warehouse');






test('create org stock', function () {

    $stock = StoreStock::make()->action(
        $this->group,
        array_merge(Stock::factory()->definition(), [
           'state' => StockStateEnum::ACTIVE
       ])
    );

    $orgStock = StoreOrgStock::make()->action(
        $this->organisation,
        $stock
    );

    expect($orgStock)->toBeInstanceOf($orgStock::class)
        ->and($orgStock->code)->toBe($stock->code)
        ->and($orgStock->name)->toBe($stock->name)
        ->and($orgStock->unit_value)->toBe($stock->unit_value)
        ->and($this->organisation->inventoryStats->number_org_stocks)->toBe(1)
        ->and($this->organisation->inventoryStats->number_current_org_stocks)->toBe(1);

    return $orgStock;
});




test('update org stock', function (OrgStock $orgStock) {

    $orgStock = UpdateOrgStock::make()->action(
        orgStock: $orgStock,
        modelData: [
            'last_fetched_at' => now()->addDay()
        ],
        strict: false
    );

    expect($orgStock)->toBeInstanceOf(OrgStock::class);

    return $orgStock;
})->depends('create org stock');

test('create org stock family', function () {

    $stockFamily = StoreStockFamily::make()->action(
        $this->group,
        StockFamily::factory()->definition()
    );
    $stock = StoreStock::make()->action(
        $stockFamily,
        array_merge(Stock::factory()->definition(), [
            'state' => StockStateEnum::ACTIVE
        ])
    );

    /** @var StockFamily $stockFamily */
    $stockFamily = $stock->stockFamily;
    expect($stockFamily)->toBeInstanceOf(StockFamily::class);
    $orgStockFamily = StoreOrgStockFamily::make()->action($this->organisation, $stockFamily, []);
    expect($orgStockFamily)->toBeInstanceOf(OrgStockFamily::class)
        ->and($orgStockFamily->state)->toBe(OrgStockFamilyStateEnum::ACTIVE)
        ->and($this->organisation->inventoryStats->number_org_stock_families)->toBe(1)
        ->and($this->organisation->inventoryStats->number_org_stocks)->toBe(1)
        ->and($this->organisation->inventoryStats->number_current_org_stocks)->toBe(1);
});

test('create org stock from 2nd stock (within stock family)', function () {


    /** @var Stock $stock */
    $stock = Stock::find(2);
    expect($stock)->toBeInstanceOf(Stock::class);

    /** @var StockFamily $stockFamily */
    $stockFamily = $stock->stockFamily;
    expect($stockFamily)->toBeInstanceOf(StockFamily::class);


    $orgStockFamily = $stockFamily->orgStockFamilies()->where('organisation_id', $this->organisation->id)->first();
    expect($orgStockFamily)->toBeInstanceOf(OrgStockFamily::class);
    $orgStock = StoreOrgStock::make()->action(
        $orgStockFamily,
        $stock
    );
    $this->organisation->refresh();
    expect($orgStock)->toBeInstanceOf($orgStock::class)
        ->and($orgStock->orgStockFamily)->toBeInstanceOf(OrgStockFamily::class)
        ->and($orgStock->orgStockFamily->state)->toBe(OrgStockFamilyStateEnum::ACTIVE)
        ->and($this->organisation->inventoryStats->number_org_stock_families)->toBe(1)
        ->and($this->organisation->inventoryStats->number_org_stocks)->toBe(2)
        ->and($this->organisation->inventoryStats->number_current_org_stocks)->toBe(2);

    return $orgStock;
});




test('attach org-stock to location', function (Location $location) {
    $orgStocks = OrgStock::all();
    expect($orgStocks->count())->toBe(2);
    $locationOrgStocks = [];
    foreach ($orgStocks as $orgStock) {
        $locationOrgStocks[] = StoreLocationOrgStock::make()->action($orgStock, $location, [
            'type' => LocationStockTypeEnum::PICKING
        ]);
    }

    expect($location->stats->number_org_stock_slots)->toBe(2)
        ->and($locationOrgStocks[0])->toBeInstanceOf(LocationOrgStock::class);

    return $locationOrgStocks[0];
})->depends('create location in warehouse area');

test('update location org stock', function (LocationOrgStock $locationOrgStock) {
    $locationOrgStock = UpdateLocationOrgStock::make()->action($locationOrgStock, [
        'type' => LocationStockTypeEnum::STORING
    ]);

    expect($locationOrgStock)->toBeInstanceOf(LocationOrgStock::class)
        ->and($locationOrgStock->type)->toBe(LocationStockTypeEnum::STORING);
    return $locationOrgStock;
})->depends('attach org-stock to location');


test('detach stock from location', function (LocationOrgStock $locationOrgStock) {
    $location = $locationOrgStock->location;
    DeleteLocationOrgStock::make()->action($locationOrgStock);
    $location->refresh();
    expect($location->stats->number_org_stock_slots)->toBe(1);
})->depends('attach org-stock to location');


test('audit stock in location', function () {
    $locationOrgStock = LocationOrgStock::first();
    $locationOrgStock = AuditLocationOrgStock::run($locationOrgStock, [
        'quantity' => 2
    ]);
    expect($locationOrgStock->quantity)->toBe(2);
});

test('move stock location', function () {
    /** @var LocationOrgStock $currentLocation */
    $currentLocation = LocationOrgStock::first();
    $targetLocation  = LocationOrgStock::latest()->first();

    expect($currentLocation->quantity)->toBeNumeric(2)
        ->and($targetLocation->quantity)->toBeNumeric(0);

    $currentLocation = MoveOrgStockToOtherLocation::make()->action($currentLocation, $targetLocation, [
        'quantity' => 1
    ]);
    $targetLocation->refresh();
    expect($currentLocation->quantity)->toBeNumeric(1)
        ->and($targetLocation->quantity)->toBeNumeric(1);
})->depends('detach stock from location');

test('update location', function ($location) {
    $location = UpdateLocation::make()->action($location, ['code' => 'AE-3']);
    expect($location->code)->toBe('AE-3');
})->depends('create location in warehouse area');


test('add found stock', function ($location) {
    $lostAndFound = AddLostAndFoundOrgStock::make()->action(
        $location,
        array_merge(LostAndFoundStock::factory()->definition(), [
            'type' => LostAndFoundOrgStockStateEnum::FOUND->value
        ])
    );

    expect($lostAndFound->type)->toBe(LostAndFoundOrgStockStateEnum::FOUND->value);

    return $lostAndFound;
})->depends('create location in warehouse area');

test('add lost stock', function ($location) {
    $lostAndFound = AddLostAndFoundOrgStock::make()->action(
        $location,
        array_merge(LostAndFoundStock::factory()->definition(), [
            'type' => LostAndFoundOrgStockStateEnum::LOST->value
        ])
    );

    expect($lostAndFound->type)->toBe(LostAndFoundOrgStockStateEnum::LOST->value);

    return $lostAndFound;
})->depends('create location in warehouse area');

test('add tag to location', function ($location) {
    $tag = StoreTag::make()->action([
        'name' => 'TAG',
        'type' => 'inventory'
    ]);

    expect($tag)->toBeInstanceOf(Tag::class);

    $location = SyncTagsLocation::make()->action($location, [
        'tags' => [$tag->tag_slug],
    ]);


    expect($location)->toBeInstanceOf(Location::class)
        ->and($location->tags)->not->toBeNull()
        ->and($location->tags()->count())->toBe(1);

    return $location;

})->depends('create location in warehouse area');

test('remove lost stock', function ($lostAndFoundStock) {
    $lostAndFound = RemoveLostAndFoundStock::make()->action($lostAndFoundStock, 2);
    expect($lostAndFound->quantity)->toBe(2.0);
})->depends('add lost stock');

test('remove found stock', function ($lostAndFoundStock) {
    $lostAndFound = RemoveLostAndFoundStock::make()->action($lostAndFoundStock, 2);
    expect($lostAndFound->quantity)->toBe(2.0);
})->depends('add found stock');



test('hydrate warehouses', function (Warehouse $warehouse) {
    HydrateWarehouse::run($warehouse);
    $this->artisan('hydrate:warehouses')->assertExitCode(0);
})->depends('create warehouse');


test('hydrate warehouse areas', function () {
    HydrateWarehouseArea::run(WarehouseArea::first());
    $this->artisan('hydrate:warehouse-areas')->assertExitCode(0);
});

test('hydrate locations', function () {
    HydrateLocation::run(Location::first());
    $this->artisan('hydrate:locations')->assertExitCode(0);
});
