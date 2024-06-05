<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Jan 2024 15:08:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Web\Website\LaunchWebsite;
use App\Actions\Web\Website\StoreWebsite;
use App\Actions\Web\Website\UI\DetectWebsiteFromDomain;
use App\Actions\Web\Website\UpdateWebsite;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Enums\Web\Website\WebsiteTypeEnum;
use App\Models\Web\Webpage;
use App\Models\Web\Website;

use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB('test_base_database.dump');
});
beforeEach(function () {
    list(
        $this->organisation,
        $this->user,
        $this->shop
    )                 = createShop();
    $this->warehouse  = createWarehouse();
    $this->fulfilment = createFulfilment($this->organisation);

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->user);
});

test('create b2b website', function () {
    $website = StoreWebsite::make()->action(
        $this->shop,
        Website::factory()->definition()
    );

    expect($website)->toBeInstanceOf(Website::class)
        ->and($website->storefront)->toBeInstanceOf(Webpage::class)
        ->and($website->webStats->number_webpages)->toBe(4);


    return $website;
});

test('launch website', function (Website $website) {
    $website = LaunchWebsite::make()->action($website);
    $website->refresh();

    expect($website->state)->toBe(WebsiteStateEnum::LIVE);
})->depends('create b2b website');


test('update website', function (Website $website) {
    $updateData = [
        'name' => 'Test Website Updated',
    ];

    $shop = UpdateWebsite::make()->action($website, $updateData);
    $shop->refresh();

    expect($shop->name)->toBe('Test Website Updated');
})->depends('create b2b website');


test('create fulfilment website', function () {
    $website = StoreWebsite::make()->action(
        $this->fulfilment->shop,
        Website::factory()->definition()
    );


    expect($website)->toBeInstanceOf(Website::class)
        ->and($website->type)->toBe(WebsiteTypeEnum::FULFILMENT)
        ->and($website->storefront)->toBeInstanceOf(Webpage::class)
        ->and($website->webStats->number_webpages)->toBe(4);

    return $website;
});

test('visit public website', function (Website $website) {
    Config::set('inertia.testing.page_paths', [resource_path('js/Pages/Iris')]);
    DetectWebsiteFromDomain::shouldRun()->with('localhost')->andReturn($website);


    $response = get(
        route(
            'iris.home'
        )
    );
    $response->assertStatus(307);
    $response->assertRedirect('disclosure/under-construction');

    $redirect = $this->followRedirects($response);
    $redirect->assertStatus(200);


    $redirect->assertInertia(function (AssertableInertia $page) {
        $page->component('Disclosure/UnderConstruction');
    });
})->depends('create fulfilment website');


test('launch fulfilment website from command', function (Website $website) {
    $this->artisan('website:launch', ['website' => $website->slug])
        ->expectsOutput('Website launched 🚀')
        ->assertExitCode(0);
    $website->refresh();

    expect($website->state)->toBe(WebsiteStateEnum::LIVE);

    return $website;
})->depends('create fulfilment website');

test('hydrate website from command', function (Website $website) {
    $this->artisan('website:hydrate', [
        'organisations' => $this->organisation->slug,
        '--slugs'       => $website->slug
    ])
        ->assertExitCode(0);
    $website->refresh();

    expect($website->webStats->number_webpages)->toBe(4);
})->depends('launch fulfilment website from command');



test('can show fulfilment website', function (Website $website) {
    $response = get(
        route(
            'grp.org.fulfilments.show.web.websites.show',
            [
                $this->organisation->slug,
                $this->fulfilment->slug,
                $website->slug
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Web/Website')
            ->has('title')
            ->has('breadcrumbs', 2);
    });
})->depends('create fulfilment website');

test('can show webpages list in fulfilment website', function (Website $website) {
    $response = get(
        route(
            'grp.org.fulfilments.show.web.webpages.index',
            [
                $this->organisation->slug,
                $this->fulfilment->slug,
                $website->slug
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Web/Webpages')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('data.data', 4);
    });
})->depends('create fulfilment website');
