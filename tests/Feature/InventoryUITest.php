<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 07-10-2024, Bali, Indonesia
 * Github: https://github.com/ganes556
 * Copyright: 2024
 *
*/

use App\Actions\Inventory\Warehouse\StoreWarehouse;
use App\Models\Inventory\Warehouse;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {

    $this->organisation = createOrganisation();
    $this->adminGuest   = createAdminGuest($this->organisation->group);


    $warehouse = Warehouse::first();
    if (!$warehouse) {
        data_set($storeData, 'code', 'CODE');
        data_set($storeData, 'name', 'NAME');

        $warehouse = StoreWarehouse::make()->action(
            $this->organisation,
            $storeData
        );
    }
    $this->warehouse = $warehouse;

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->getUser());
});


test('UI Index locations', function () {
    $response = get(route('grp.org.warehouses.show.infrastructure.locations.index', [$this->organisation->slug, $this->warehouse->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Warehouse/Locations')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'locations')
                        ->etc()
            )
            ->has('tagRoute')
            ->has('tagsList')
            ->has('data');
    });
});

test('UI Create location', function () {
    $response = get(route('grp.org.warehouses.show.infrastructure.locations.create', [$this->organisation->slug, $this->warehouse->slug,]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'new location')
                        ->etc()
            )
            ->has('formData');
    });
});
