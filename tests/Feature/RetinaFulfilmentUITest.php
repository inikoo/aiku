<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 19 Jun 2024 09:24:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\CRM\WebUser\StoreWebUser;
use App\Actions\Web\Website\LaunchWebsite;
use App\Actions\Web\Website\UI\DetectWebsiteFromDomain;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\WebUser;
use Inertia\Testing\AssertableInertia;


use function Pest\Laravel\actingAs;

beforeAll(function () {
    loadDB();
});
beforeEach(function () {
    $this->organisation      = createOrganisation();
    $this->warehouse         = createWarehouse();
    $this->fulfilment        = createFulfilment($this->organisation);
    $this->fulfilmentWebsite = createWebsite($this->fulfilment->shop);

    LaunchWebsite::make()->action($this->fulfilmentWebsite);

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
        data_set($storeData, 'password', 'test');

        $webUser = StoreWebUser::make()->action(
            $this->customer,
            $storeData
        );
    }
    $this->webUser = $webUser;

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Retina')]
    );
  //  actingAs($this->webUser);
    DetectWebsiteFromDomain::shouldRun()->with('localhost')->andReturn($this->fulfilmentWebsite);

});

 test('show log in', function () {

     $this->withoutExceptionHandling();
     $response = $this->get(route('retina.login.show'));
     $response->assertInertia(function (AssertableInertia $page) {
         $page->component('Auth/Login');
     });
 });

test('should not show retina without authentication', function () {
    $response= $this->get(route('retina.home'));
    expect($response)->toHaveStatus(302);
});

test('show retina when authenticated', function () {



    $this->actingAs($this->webUser);

    $this->withoutExceptionHandling();
    $response=$this->get(route('retina.home'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('Dashboard/Dashboard');
    });
});
