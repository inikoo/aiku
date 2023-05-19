<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Leads\Prospect\StoreProspect;
use App\Actions\Leads\Prospect\UpdateProspect;
use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Models\Helpers\Address;
use App\Models\Leads\Prospect;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;

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

test('create prospect', function () {
    $shop     = StoreShop::make()->action(Shop::factory()->definition());
    $prospect = StoreProspect::make()->action($shop, Prospect::factory()->definition(), Address::factory()->definition());
    $this->assertModelExists($prospect);
    return $prospect;
});

test('update prospect', function () {
    $prospect        = Prospect::latest()->first();
    $prospectUpdated = UpdateProspect::run($prospect, Prospect::factory()->definition());
    $this->assertModelExists($prospectUpdated);
})->depends('create prospect');
