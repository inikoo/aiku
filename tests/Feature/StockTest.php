<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 10:33:43 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Inventory\Stock\StoreStock;
use App\Actions\Inventory\Stock\UpdateStock;
use App\Models\Inventory\Stock;
use App\Models\Tenancy\Tenant;

beforeAll(fn() => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
});

test('create new stock', function () {
    $stock = StoreStock::run(app('currentTenant'), Stock::factory()->definition());

    $this->assertModelExists($stock);

    return $stock;
});

test('update stock', function ($stock) {
    $stock = UpdateStock::run($stock, Stock::factory()->definition());

    $this->assertModelExists($stock);
})->depends('create new stock');
