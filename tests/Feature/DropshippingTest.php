<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 16:30:57 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\CRM\CustomerClient\StoreCustomerClient;
use App\Actions\CRM\CustomerClient\UpdateCustomerClient;
use App\Actions\Dropshipping\DropshippingCustomerPortfolio\StoreDropshippingCustomerPortfolio;
use App\Actions\Dropshipping\DropshippingCustomerPortfolio\UpdateDropshippingCustomerPortfolio;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Shop;

use App\Models\Dropshipping\CustomerClient;
use App\Models\Dropshipping\DropshippingCustomerPortfolio;
use App\Models\Ordering\Platform;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

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

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->user);
});

test('test platforms were seeded ', function () {
    $this->assertDatabaseCount('platforms', 2);
    $platform=Platform::first();
    expect($platform)->toBeInstanceOf(Platform::class)
        ->and($platform->stats)->toBeInstanceOf(PlatformStats::class);
})->todo();

test('create customer client', function () {
    $customerClient = StoreCustomerClient::make()->action($this->customer, CustomerClient::factory()->definition());
    expect($customerClient)->toBeInstanceOf(CustomerClient::class);

    return $customerClient;
});

test('update customer client', function ($customerClient) {
    $customerClient = UpdateCustomerClient::make()->action($customerClient, ['reference' => '001']);
    expect($customerClient->reference)->toBe('001');
})->depends('create customer client');

test('add product to customer portfolio', function () {
    $dropshippingCustomerPortfolio = StoreDropshippingCustomerPortfolio::make()->action(
        $this->customer,
        [
            'product_id' => $this->product->id
        ]
    );
    expect($dropshippingCustomerPortfolio)->toBeInstanceOf(DropshippingCustomerPortfolio::class);

    return $dropshippingCustomerPortfolio;
});

test('update customer portfolio', function (DropshippingCustomerPortfolio $dropshippingCustomerPortfolio) {
    $dropshippingCustomerPortfolio = UpdateDropshippingCustomerPortfolio::make()->action(
        $dropshippingCustomerPortfolio,
        [
            'reference' => 'new_reference'
        ]
    );
    expect($dropshippingCustomerPortfolio->reference)->toBe('new_reference');

    return $dropshippingCustomerPortfolio;
})->depends('add product to customer portfolio');


test('update group dropshipping_integration_token', function () {
    expect($this->group->dropshipping_integration_token)->toHaveLength(34)->toStartWith('1:');
    $command = join(
        ' ',
        [
            'group:seed-integration-token',
            $this->group->id.':test_token'
        ]
    );
    $this->group->refresh();
    $this->artisan($command)->assertExitCode(0);
    expect($this->group->dropshipping_integration_token)->not->toBe('test_token');
});

test('get dropshipping access token', function () {
    $token = $this->group->dropshipping_integration_token;

    $response = postJson(
        route(
            'ds_api.connect',
            [
                'token' => $token
            ]
        )
    );


    $response->assertOk();
    $response->assertJsonStructure([
        'api-key',
    ]);

    $this->token = $response->json('token');
});

test('api get dropshipping shops', function () {
    Sanctum::actingAs($this->group);

    $response = getJson(
        route(
            'ds_api.shops.index'
        )
    );


    $response->assertOk();
    $response->assertJsonStructure(['data']);
    $response->assertJsonCount(1, 'data');

    return $response->json('data.0.id');
});

test('api show shop', function ($shopId) {
    Sanctum::actingAs($this->group);


    $response = getJson(
        route(
            'ds_api.shops.show',
            [$shopId]
        )
    );

    $response->assertOk();
    $response->assertJsonStructure(['data']);
})->depends('api get dropshipping shops');

test('api index customers', function ($shopId) {
    Sanctum::actingAs($this->group);

    $response = getJson(
        route(
            'ds_api.shops.show.customers.index',
            [$shopId]
        )
    );

    $response->assertOk();
    $response->assertJsonStructure(['data']);
})->depends('api get dropshipping shops');

test('api show customer', function () {
    Sanctum::actingAs($this->group);


    $response = getJson(
        route(
            'ds_api.customers.show',
            [$this->customer->id]
        )
    );

    $response->assertOk();
    $response->assertJsonStructure(['data']);
});

test('api index products in shop', function ($shopId) {
    Sanctum::actingAs($this->group);
    $response = getJson(
        route(
            'ds_api.shops.show.products.index',
            [$shopId]
        )
    );

    $response->assertOk();
    $response->assertJsonStructure(['data']);
})->depends('api get dropshipping shops');

test('api show product', function () {
    Sanctum::actingAs($this->group);
    $response = getJson(
        route(
            'ds_api.products.show',
            [$this->product->id]
        )
    );

    $response->assertOk();
    $response->assertJsonStructure(['data']);
});

test('api index products in customer ', function ($shopId) {
    Sanctum::actingAs($this->group);
    $response = getJson(
        route(
            'ds_api.customers.show.products.index',
            [$shopId]
        )
    );

    $response->assertOk();
    $response->assertJsonStructure(['data']);
})->depends('api get dropshipping shops');

test('api index customers in product', function () {
    Sanctum::actingAs($this->group);
    $response = getJson(
        route(
            'ds_api.products.show.customers.index',
            [$this->product->id]
        )
    );

    $response->assertOk();
    $response->assertJsonStructure(['data']);
});
