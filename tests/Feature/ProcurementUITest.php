<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 13 Jun 2024 13:27:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\Inventory\Location\StoreLocation;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Inventory\Location;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB();
});


beforeEach(function () {

    $this->organisation = createOrganisation();
    $this->adminGuest   = createAdminGuest($this->organisation->group);


    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->user);
});

test('UI Index org suppliers', function () {
    $response = $this->get(route('grp.org.procurement.org_suppliers.index', [$this->organisation->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Procurement/OrgSuppliers')
            ->has('title')
            ->has('breadcrumbs', 3);
    });
});
