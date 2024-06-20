<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 19 Jun 2024 08:07:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Shop;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->group        = $this->organisation->group;
    $this->user         = createAdminGuest($this->group)->user;


    $shop = Shop::first();
    if (!$shop) {
        $storeData = Shop::factory()->definition();
        data_set($storeData, 'type', ShopTypeEnum::DROPSHIPPING);

        $shop = StoreShop::make()->action(
            $this->organisation,
            $storeData
        );
    }
    $this->shop = $shop;

    $this->shop = UpdateShop::make()->action($this->shop, ['state' => ShopStateEnum::OPEN]);

    $this->customer = createCustomer($this->shop);

    list(
        $this->tradeUnit,
        $this->product
    ) = createProduct($this->shop);

    $this->department = $this->product->department;
    $this->family = $this->product->family;

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->user);
});

// Department

test('UI Index catalogue departments', function () {
    $response = get(route('grp.org.shops.show.catalogue.departments.index', [$this->organisation->slug, $this->shop->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/Departments')
            ->has('title')
            ->has('breadcrumbs', 3);
    });
});

test('UI show department', function () {
    $response = get(route('grp.org.shops.show.catalogue.departments.show', [$this->organisation->slug, $this->shop->slug, $this->department->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/Department')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('navigation')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->product->department->name)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI create department', function () {
    $response = get(route('grp.org.shops.show.catalogue.departments.create', [$this->organisation->slug, $this->shop->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 4);
    });
});

test('UI edit department', function () {
    $response = get(route('grp.org.shops.show.catalogue.departments.edit', [$this->organisation->slug,  $this->shop->slug, $this->department->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 2)
            ->has('pageHead')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.org.catalogue.departments.update')
                        ->where('parameters', [
                            'organisation' => $this->department->organisation_id, 
                            'shop' => $this->department->shop_id, 
                            'productCategory' => $this->department->id
                            ])
            )
            ->has('breadcrumbs', 3);
    });
});

