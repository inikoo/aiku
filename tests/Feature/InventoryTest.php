<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Inventory\Warehouse\StoreWarehouse;
use App\Actions\Inventory\Warehouse\UpdateWarehouse;
use App\Actions\Inventory\WarehouseArea\StoreWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UpdateWarehouseArea;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

test('create and update warehouse', function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
    $warehouse = StoreWarehouse::make()->action(Warehouse::factory()->definition());
    $this->assertModelExists($warehouse);
    $warehouse = UpdateWarehouse::make()->action($warehouse, ['name' => 'Pika Ltd']);
    expect($warehouse->name)->toBe('Pika Ltd');

});

test('create and update warehouse area', function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
    $warehouse = Warehouse::find(1);
    $this->assertModelExists($warehouse);
    $warehouseArea = StoreWarehouseArea::make()->action($warehouse, WarehouseArea::factory()->definition());
    $this->assertModelExists($warehouseArea);
    $warehouse = UpdateWarehouseArea::make()->action($warehouseArea, ['name' => 'Pika Ltd']);
    expect($warehouse->name)->toBe('Pika Ltd');

});
