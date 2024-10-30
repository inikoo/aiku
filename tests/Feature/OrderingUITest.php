<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 30-10-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

use App\Actions\Ordering\ShippingZoneSchema\StoreShippingZoneSchema;
use App\Models\Ordering\ShippingZoneSchema;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses()->group('ui');

beforeAll(function () {
    loadDB();
});


beforeEach(function () {
    list(
        $this->organisation,
        $this->user,
        $this->shop
    ) = createShop();

    $this->adminGuest   = createAdminGuest($this->organisation->group);

    $shippingZoneSchema = ShippingZoneSchema::first();
    if (!$shippingZoneSchema) {
        $shippingZoneSchema = StoreShippingZoneSchema::make()->action($this->shop, ShippingZoneSchema::factory()->definition());
    }
    $this -> shippingZoneSchema = $shippingZoneSchema;

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->getUser());
});



test('UI index asset shipping', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.shops.show.assets.shipping.index', [$this->organisation, $this->shop]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/Shippings')
            ->where('title', 'shipping')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Shipping')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI create asset shipping', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.shops.show.assets.shipping.create', [$this->organisation, $this->shop]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->where('title', 'new schema')
            ->has('breadcrumbs', 4)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'new schema')
                        ->etc()
            )
            ->has('formData');
    });
});

test('UI show asset shipping', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.shops.show.assets.shipping.show', [$this->organisation, $this->shop, $this->shippingZoneSchema]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/ShippingZoneSchema')
            ->where('title', 'Shipping Zone Schema')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->shippingZoneSchema->name)
                        ->etc()
            )
            ->has('navigation')
            ->has('tabs');
    });
});
