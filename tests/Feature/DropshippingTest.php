<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 16:30:57 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\StoreShop;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Shop;

use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeAll(function () {
    loadDB('test_base_database.dump');
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->group        = $this->organisation->group;
    $this->user         = createAdminGuest($this->group)->user;


    $dropshippingShop = Shop::first();
    if (!$dropshippingShop) {
        $storeData = Shop::factory()->definition();
        data_set($storeData, 'type', ShopTypeEnum::DROPSHIPPING);

        $dropshippingShop = StoreShop::make()->action(
            $this->organisation,
            $storeData
        );
    }
    $this->dropshippingShop = $dropshippingShop;


    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->user);
});

test('update group dropshipping_integration_token', function () {
    expect($this->group->dropshipping_integration_token)->toHaveLength(34)->toStartWith('1:');
    $command = join(
        ' ',
        [
            'group:seed-integration-token',
            $this->group->slug,
            'test_token'
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
            'dropshipping.connect',
            [
                'dropshipping_integration_token' => $token
            ]
        )
    );


    $response->assertOk();
    $response->assertJsonStructure([
        'token',
    ]);

    $this->token = $response->json('token');
});

test('get dropshipping shops', function () {
    Sanctum::actingAs($this->group);

    $response = getJson(
        route(
            'dropshipping.shops.index'
        )
    );

    $response->assertOk();
    $response->assertJsonStructure(['data']);
    $response->assertJsonCount(1, 'data');

});
