<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 13 Jun 2024 13:27:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Inventory\Location\StoreLocation;
use App\Enums\UI\Fulfilment\FulfilmentAssetsTabsEnum;
use App\Models\Inventory\Location;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB('test_base_database.dump');
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->adminGuest   = createAdminGuest($this->organisation->group);
    $this->warehouse    = createWarehouse();
    $this->fulfilment   = createFulfilment($this->organisation);
    $location           = $this->warehouse->locations()->first();
    if (!$location) {
        StoreLocation::run(
            $this->warehouse,
            Location::factory()->definition()
        );
        StoreLocation::run(
            $this->warehouse,
            Location::factory()->definition()
        );
    }


    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->user);
});

test('UI Index fulfilment assets', function () {
    $response = get(route('grp.org.fulfilments.show.assets.index', [$this->organisation->slug, $this->fulfilment->slug ]));
    expect(FulfilmentAssetsTabsEnum::DASHBOARD->value)->toBe('dashboard');
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Products')
            ->has('title')->has('tabs')
            ->has('breadcrumbs', 3);
    });
});