<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 13 Jun 2024 13:27:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Analytics\GetSectionRoute;
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\WebUser\StoreWebUser;
use App\Actions\Dropshipping\CustomerClient\StoreCustomerClient;
use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Ordering\Order\StoreOrder;
use App\Actions\Web\Website\StoreWebsite;
use App\Enums\Analytics\AikuSection\AikuSectionEnum;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Analytics\AikuScopedSection;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Inventory\Location;
use App\Models\Ordering\Order;
use App\Models\Web\Website;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB();
});

beforeEach(
    /**
     * @throws \Throwable
     */
    function () {

        $this->organisation = createOrganisation();
        $this->adminGuest   = createAdminGuest($this->organisation->group);
        $this->warehouse    = createWarehouse();
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

        $shop = Shop::first();
        if (!$shop) {
            $storeData = Shop::factory()->definition();
            data_set($storeData, 'type', ShopTypeEnum::DROPSHIPPING);
            data_set($storeData, 'warehouses', [$this->warehouse->id]);

            $shop = StoreShop::make()->action(
                $this->organisation,
                $storeData
            );
        }
        $this->shop = $shop;

        $this->shop = UpdateShop::make()->action($this->shop, ['state' => ShopStateEnum::OPEN]);


        $website = Website::first();
        if (!$website) {
            $storeData = Website::factory()->definition();

            $website = StoreWebsite::make()->action(
                $this->shop,
                $storeData
            );
        }
        $this->website = $website;

        $this->shop->refresh();

        $customer = Customer::first();
        if (!$customer) {
            $storeData = Customer::factory()->definition();

            $customer = StoreCustomer::make()->action(
                $this->shop,
                $storeData
            );
        }
        $this->customer = $customer;

        $webUser = WebUser::first();
        if (!$webUser) {
            $webUser = StoreWebUser::make()->action(
                $this->customer,
                [
                    'email' => 'example@mail.com',
                    'username' => 'example',
                    'password' => 'password',
                    'is_root' => true,
                ]
            );
        }
        $this->webUser = $webUser;

        $customerClient = CustomerClient::first();
        if (!$customerClient) {
            $storeData = CustomerClient::factory()->definition();

            $customerClient = StoreCustomerClient::make()->action(
                $this->customer,
                $storeData
            );
        }
        $this->customerClient = $customerClient;

        $order = Order::first();
        if (!$order) {
            $order = StoreOrder::make()->action(
                $this->customer,
                []
            );
        }
        $this->order = $order;

        $this->adminGuest->refresh();

        Config::set(
            'inertia.testing.page_paths',
            [resource_path('js/Pages/Grp')]
        );
        actingAs($this->adminGuest->getUser());
    }
);

test('UI Index customers', function () {
    $response = $this->get(route('grp.org.shops.show.crm.customers.index', [$this->organisation->slug, $this->shop->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Shop/CRM/Customers')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'customers')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI create customer', function () {
    $response = get(route('grp.org.shops.show.crm.customers.create', [$this->organisation->slug, $this->shop->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 4);
    });
});

test('UI show customer', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.shops.show.crm.customers.show', [$this->organisation->slug, $this->shop->slug, $this->customer->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Shop/CRM/Customer')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->customer->name)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI edit customer', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.shops.show.crm.customers.edit', [$this->organisation->slug, $this->shop->slug, $this->customer->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('pageHead')
            ->has('formData')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.customer.update')
                        ->where('parameters', [$this->customer->id])
            )
            ->has('breadcrumbs', 3);
    });
});

// test('UI edit employee', function () {
//     $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/shops/'.$this->shop->slug.'/crm/customers/'.$this->customer->slug.'/edit?section=properties');
//     $response->assertInertia(function (AssertableInertia $page) {
//         $page
//             ->component('EditModel')
//             ->has('title')
//             ->has('pageHead')
//             ->has('formData')
//             ->has(
//                 'formData.args.updateRoute',
//                 fn (AssertableInertia $page) => $page
//                         ->where('name', 'grp.models.customer.update')
//                         ->where('parameters', $this->customer->id)
//             )
//             ->has('breadcrumbs', 3);
//     });
// })->skip();

test('UI Index customer clients', function () {
    $response = $this->get(route('grp.org.shops.show.crm.customers.show.customer-clients.index', [$this->organisation->slug, $this->shop->slug, $this->customer->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Shop/CRM/CustomerClients')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->customer->name)
                        ->has('subNavigation')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI Show customer client', function () {
    $response = $this->get(route('grp.org.shops.show.crm.customers.show.customer-clients.show', [$this->organisation->slug, $this->shop->slug, $this->customer->slug, $this->customerClient->ulid]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Shop/CRM/CustomerClient')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->customerClient->name)
                        ->has('subNavigation')
                        ->etc()
            );
    });
});

test('UI create customer client', function () {
    $response = get(route('grp.org.shops.show.crm.customers.show.customer-clients.create', [$this->organisation->slug, $this->shop->slug, $this->customer->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 5);
    });
});

test('UI edit customer client', function () {
    $response = get(route('grp.org.shops.show.crm.customers.show.customer-clients.edit', [$this->organisation->slug, $this->shop->slug, $this->customer->slug, $this->customerClient]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->where('title', 'edit client')
            ->has(
                'formData',
                fn (AssertableInertia $form) => $form
                    ->has('blueprint', 1)
                    ->where('blueprint.0.title', 'contact')
                    ->has('blueprint.0.fields.company_name')
                    ->where('blueprint.0.fields.company_name.label', 'company')
                    ->where('blueprint.0.fields.company_name.value', $this->customerClient->company_name)
                    ->has('blueprint.0.fields.contact_name')
                    ->where('blueprint.0.fields.contact_name.label', 'contact name')
                    ->where('blueprint.0.fields.contact_name.value', $this->customerClient->contact_name)
                    ->has('blueprint.0.fields.email')
                    ->where('blueprint.0.fields.email.label', 'email')
                    ->where('blueprint.0.fields.email.value', $this->customerClient->email)
                    ->has('blueprint.0.fields.phone')
                    ->where('blueprint.0.fields.phone.label', 'phone')
                    ->where('blueprint.0.fields.phone.value', $this->customerClient->phone)
                    ->etc()
            )
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'edit client')
                        ->has('actions')
                        ->etc()
            )
            ->has('breadcrumbs', 5);
    });
});

test('UI Index customer portfolios', function () {
    $response = $this->get(route('grp.org.shops.show.crm.customers.show.portfolios.index', [$this->organisation->slug, $this->shop->slug, $this->customer->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Shop/CRM/Portfolios')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->customer->name)
                        ->has('subNavigation')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI Index customer web users', function () {
    $response = $this->get(route('grp.org.shops.show.crm.customers.show.web-users.index', [$this->organisation->slug, $this->shop->slug, $this->customer->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Shop/CRM/WebUsers')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->customer->name)
                        ->has('subNavigation')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI Create customer web users', function () {
    $response = $this->get(route('grp.org.shops.show.crm.customers.show.web-users.create', [$this->organisation->slug, $this->shop->slug, $this->customer->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Create web user')
                        ->etc()
            )
            ->has('formData');
    });
});

test('UI show customer web users', function () {
    $response = $this->get(route('grp.org.shops.show.crm.customers.show.web-users.show', [$this->organisation->slug, $this->shop->slug, $this->customer->slug, $this->webUser->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Shop/CRM/WebUser')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->webUser->username)
                        ->etc()
            )
            ->has('data');
    });
});

test('UI Edit customer web users', function () {
    $response = $this->get(route('grp.org.shops.show.crm.customers.show.web-users.edit', [$this->organisation->slug, $this->shop->slug, $this->customer->slug, $this->webUser->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Edit web user')
                        ->etc()
            )
            ->has('formData');
    });
});

test('UI Index customer orders', function () {
    $response = $this->get(route('grp.org.shops.show.crm.customers.show.orders.index', [$this->organisation->slug, $this->shop->slug, $this->customer->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Ordering/Orders')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->customer->name)
                        ->has('subNavigation')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI show order', function () {
    $response = $this->get(route('grp.org.shops.show.crm.customers.show.orders.show', [$this->organisation->slug, $this->shop->slug, $this->customer->slug, $this->order->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Ordering/Order')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->order->reference)
                        ->etc()
            )
            ->has('data');
    });
});

test('can show list of mailshots', function () {
    $shop     = $this->shop;
    $organisation = $this->organisation;
    $response = get(route('grp.org.shops.show.marketing.mailshots.index', [$organisation->slug, $shop->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Mail/Mailshots')
            ->has('title');
    });
});

test('can show list of prospects', function () {
    $shop     = $this->shop;
    $organisation = $this->organisation;
    $response = get(route('grp.org.shops.show.crm.prospects.index', [$organisation->slug, $shop->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Shop/CRM/Prospects')
            ->has('title');
    });
});

test('can show list of tags', function () {
    $shop     = $this->shop;
    $organisation = $this->organisation;
    $response = get(route('grp.org.shops.show.crm.prospects.tags.index', [$organisation->slug, $shop->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Shop/CRM/Tags')
            ->has('title');
    });
});

test('UI get section route crm dashboard', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.shops.show.crm.customers.index', [
        'organisation' => $this->organisation->slug,
        'shop' => $this->shop->slug
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::SHOP_CRM->value)
        ->and($sectionScope->model_slug)->toBe($this->shop->slug);
});

test('UI get section route client dropshipping', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.shops.show.crm.customers.show.customer-clients.index', [
        'organisation' => $this->organisation->slug,
        'shop' => $this->shop->slug,
        'customer' => $this->customer->slug
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::DROPSHIPPING->value)
        ->and($sectionScope->model_slug)->toBe($this->shop->slug);
});
