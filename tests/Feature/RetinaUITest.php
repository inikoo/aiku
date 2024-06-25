<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 19 Jun 2024 09:24:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\CRM\WebUser\StoreWebUser;
use App\Actions\Web\Website\UI\DetectWebsiteFromDomain;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\WebUser;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB();
});
beforeEach(function () {
    $this->organisation      = createOrganisation();
    $this->warehouse         = createWarehouse();
    $this->fulfilment        = createFulfilment($this->organisation);
    $this->fulfilmentWebsite = createWebsite($this->fulfilment->shop);

    $shop = Shop::first();
    if (!$shop) {
        $storeData = Shop::factory()->definition();
        data_set($storeData, 'type', ShopTypeEnum::FULFILMENT);
        data_set($storeData, 'warehouses', [$this->warehouse->id]);

        $shop = StoreShop::make()->action(
            $this->organisation,
            $storeData
        );
    }
    $this->shop = $shop;

    $this->shop = UpdateShop::make()->action($this->shop, ['state' => ShopStateEnum::OPEN]);

    $this->customer = createCustomer($this->shop);

    $webUser = WebUser::first();
    if (!$webUser) {
        data_set($storeData, 'username', 'test');
        data_set($storeData, 'email', 'test@testmail.com');
        data_set($storeData, 'password', 'testo');

        $webUser = StoreWebUser::make()->action(
            $this->customer,
            $storeData
        );
    }
    $this->webUser = $webUser;
    $website= $this->fulfilmentWebsite;

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Retina')]
    );
    DetectWebsiteFromDomain::shouldRun()->with('localhost')->andReturn($website);

    actingAs($this->webUser);
});

test('UI Index pallets', function () {
    $this->withoutExceptionHandling();
    // $this->withoutMix();
    // $this->withoutVite();
    $response = $this->get(route('retina.storage.pallets.index'));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPallets')
            ->has('title')
            ->has('pageHead')
            ->has('breadcrumbs', 3);
    });
});
