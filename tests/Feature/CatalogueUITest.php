<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 19 Jun 2024 08:07:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Collection\StoreCollection;
use App\Actions\Catalogue\ProductCategory\StoreProductCategory;
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Collection;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses()->group('ui');

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
    $this->family     = $this->product->family;


    $subDepartment = $this->shop->productCategories()->where('type', ProductCategoryTypeEnum::SUB_DEPARTMENT)->first();
    if (!$subDepartment) {
        $subDepartmentData = ProductCategory::factory()->definition();
        data_set($subDepartmentData, 'type', ProductCategoryTypeEnum::SUB_DEPARTMENT->value);
        $subDepartment = StoreProductCategory::make()->action(
            $this->department,
            $subDepartmentData
        );
    }
    $this->subDepartment = $subDepartment;

    $collection = Collection::first();
    if (!$collection) {
        data_set($storeData, 'code', 'Test');
        data_set($storeData, 'name', 'Testa');

        $collection = StoreCollection::make()->action(
            $this->shop,
            $storeData
        );
    }
    $this->collection = $collection;

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
                            'organisation'    => $this->department->organisation_id,
                            'shop'            => $this->department->shop_id,
                            'productCategory' => $this->department->id
                            ])
            )
            ->has('breadcrumbs', 3);
    });
});

test('UI show department (customers tab)', function () {
    $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/shops/'.$this->shop->slug.'/catalogue/departments/'.$this->department->slug.'?tab=customers');
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

test('UI Index catalogue family inside department', function () {
    $response = get(route('grp.org.shops.show.catalogue.departments.show.families.index', [$this->organisation->slug, $this->shop->slug, $this->department->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/Families')
            ->has('title')
            ->has('breadcrumbs', 4);
    });
});

test('UI Create catalogue family inside department', function () {
    $response = get(route('grp.org.shops.show.catalogue.departments.show.families.create', [$this->organisation->slug, $this->shop->slug, $this->department->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 5);
    });
});

test('UI show family in department', function () {
    $response = get(route('grp.org.shops.show.catalogue.departments.show.families.show', [$this->organisation->slug, $this->shop->slug, $this->department->slug, $this->family->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/Family')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('navigation')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->family->name)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI edit family in department', function () {
    $response = get(route('grp.org.shops.show.catalogue.departments.show.families.edit', [$this->organisation->slug, $this->shop->slug, $this->department->slug, $this->family->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 2)
            ->has('pageHead')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.org.catalogue.families.update')
                        ->where('parameters', [
                            'organisation'      => $this->family->organisation_id,
                            'shop'              => $this->family->shop_id,
                            'productCategory'   => $this->family->id
                            ])
            )
            ->has('breadcrumbs', 4);
    });
});

test('UI Index catalogue product inside department', function () {
    $response = get(route('grp.org.shops.show.catalogue.departments.show.products.index', [$this->organisation->slug, $this->shop->slug, $this->department->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/Products')
            ->has('title')
            ->has('breadcrumbs', 4);
    });
});

test('UI show product in department', function () {
    $response = get(route('grp.org.shops.show.catalogue.departments.show.products.show', [$this->organisation->slug, $this->shop->slug, $this->department->slug, $this->product->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/Product')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('navigation')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->product->code)
                        ->etc()
            )
            ->has('tabs');

    });
});



test('UI Index catalogue sub department inside department', function () {
    $response = get(route('grp.org.shops.show.catalogue.departments.show.sub-departments.index', [$this->organisation->slug, $this->shop->slug, $this->department->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/SubDepartments')
            ->has('title')
            ->has('breadcrumbs', 4);
    });
});

test('UI Create catalogue sub department inside department', function () {
    $response = get(route('grp.org.shops.show.catalogue.departments.show.sub-departments.create', [$this->organisation->slug, $this->shop->slug, $this->department->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 5);
    });
});

test('UI show sub department in department', function () {
    $response = get(route('grp.org.shops.show.catalogue.departments.show.sub-departments.show', [$this->organisation->slug, $this->shop->slug, $this->department->slug, $this->subDepartment->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/Department')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('navigation')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->subDepartment->name)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI edit sub department in department', function () {
    $response = get(route('grp.org.shops.show.catalogue.departments.show.sub-departments.edit', [$this->organisation->slug, $this->shop->slug, $this->department->slug, $this->subDepartment->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 2)
            ->has('pageHead')
            ->has('formData')
            ->has('breadcrumbs', 4);
    });
});

test('UI Index catalogue collection', function () {
    $response = get(route('grp.org.shops.show.catalogue.collections.index', [$this->organisation->slug, $this->shop->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/Collections')
            ->has('title')
            ->has('breadcrumbs', 4);
    });
});

test('UI Create collection', function () {
    $response = get(route('grp.org.shops.show.catalogue.collections.create', [$this->organisation->slug, $this->shop->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 5);
    });
});

test('UI show collection', function () {
    $response = get(route('grp.org.shops.show.catalogue.collections.show', [$this->organisation->slug, $this->shop->slug, $this->collection->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/Collection')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('navigation')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->collection->code)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI edit collection', function () {
    $response = get(route('grp.org.shops.show.catalogue.collections.edit', [$this->organisation->slug, $this->shop->slug, $this->collection->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 2)
            ->has('pageHead')
            ->has('formData')
            ->has('breadcrumbs', 4);
    });
});

test('UI edit product', function () {
    $response = get(route('grp.org.shops.show.catalogue.families.show.products.edit', [$this->organisation->slug, $this->shop->slug, $this->family->slug, $this->product->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 7)
            ->has('pageHead')
            ->has('formData')
            ->has('breadcrumbs', 4);
    });
});

test('UI create product', function () {
    $response = get(route('grp.org.shops.show.catalogue.families.show.products.create', [$this->organisation->slug, $this->shop->slug, $this->family->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 5)
            ->has('pageHead')
            ->has('formData')
            ->has('breadcrumbs', 5);
    });
});

test('UI Index Charges', function () {
    $response = get(route('grp.org.shops.show.assets.charges.index', [$this->organisation->slug, $this->shop->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/Charges')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('data');
    });
});

test('UI Index Insurances', function () {
    $response = get(route('grp.org.shops.show.assets.insurances.index', [$this->organisation->slug, $this->shop->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/Insurances')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('data');
    });
});
