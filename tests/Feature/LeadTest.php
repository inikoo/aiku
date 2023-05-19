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
use App\Models\Helpers\Address;
use App\Models\Leads\Prospect;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();

});

test('create prospect', function () {
    $shop = StoreShop::make()->action(Shop::factory()->definition());
    $prospect = StoreProspect::make()->action($shop, [
        'contact_name'  => 'check123',
        'company_name'  => 'check123',
        'email'         => 'test@gmail.com',
        'phone'         => '+12345678',
        'website'       => 'https://google.com'
    ], Address::factory()->definition());
    $this->assertModelExists($prospect);
    return $prospect;
});

test('update prospect', function () {
    $prospect        = Prospect::latest()->first();
    $prospectUpdated = UpdateProspect::run($prospect, Prospect::factory()->definition());
    $this->assertModelExists($prospectUpdated);
})->depends('create prospect');
