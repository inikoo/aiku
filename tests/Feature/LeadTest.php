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
use App\Actions\Sales\Customer\StoreCustomer;
use App\Enums\Leads\Prospect\ProspectStateEnum;
use App\Enums\Sales\Customer\CustomerStateEnum;
use App\Models\Helpers\Address;
use App\Models\Leads\Prospect;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Tenancy\Tenant;
use Throwable;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();

});


test('create prospect', function () {
    $shop     = StoreShop::make()->action(Shop::factory()->definition());
    try {
        $customer = StoreCustomer::make()->action(
            $shop,
            Customer::factory()->definition(),
            Address::factory()->definition()
        );
    } catch (Throwable $e) {
        echo $e->getMessage();
    }
    $customer['state'] = CustomerStateEnum::ACTIVE;
    $prospect = StoreProspect::make()->action($shop, $customer, Prospect::factory()->definition(), Address::factory()->definition());
    $this->assertModelExists($prospect);
    return $prospect;
});

test('update prospect', function () {
    $prospect = Prospect::latest()->first();
    $prospectUpdated = UpdateProspect::run($prospect, Prospect::factory()->definition());
    $this->assertModelExists($prospectUpdated);
})->depends('create prospect');

