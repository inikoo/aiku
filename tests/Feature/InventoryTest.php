<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Inventory\Warehouse\StoreWarehouse;
use App\Models\Inventory\Warehouse;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

test('create warehouse', function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();

    $warehouse = StoreWarehouse::make()->action(Warehouse::factory()->definition());
    $this->assertModelExists($warehouse);
});
