<?php

/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 13 Jun 2024 13:27:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\SupplyChain\Supplier\StoreSupplier;
use App\Models\SupplyChain\Supplier;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

beforeAll(function () {
    loadDB();
});


beforeEach(function () {
    $this->organisation    = createOrganisation();
    $this->adminGuest      = createAdminGuest($this->organisation->group);
    $this->group           = group();

    $supplier = Supplier::first();
    if (!$supplier) {
        $storeData = Supplier::factory()->definition();

        $supplier = StoreSupplier::make()->action(
            $this->group,
            $storeData
        );
    }
    $this->supplier = $supplier;


    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->getUser());
});

test('UI Index suppliers', function () {

    $this->withoutExceptionHandling();
    $response = $this->get(route('grp.supply-chain.suppliers.index'));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('SupplyChain/Suppliers')
            ->has('title')
            ->has('pageHead')
            ->has('data')
            ->has('breadcrumbs', 3);
    });
});

test('UI show supplier', function () {
    $this->withoutExceptionHandling();
    $response = $this->get(route('grp.supply-chain.suppliers.show', [$this->supplier->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('SupplyChain/Supplier')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->supplier->name)
                        ->etc()
            )
            ->has('tabs');

    });
});