<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:13:50 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Marketing\Offer\StoreOffer;
use App\Actions\Marketing\Offer\UpdateOffer;
use App\Actions\Marketing\OfferCampaign\StoreOfferCampaign;
use App\Actions\Marketing\OfferCampaign\UpdateOfferCampaign;
use App\Actions\Marketing\OfferComponent\StoreOfferComponent;
use App\Actions\Marketing\OfferComponent\UpdateOfferComponent;
use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Marketing\Offer;
use App\Models\Marketing\OfferCampaign;
use App\Models\Marketing\OfferComponent;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $tenant = Tenant::first();
    if (!$tenant) {
        $group  = StoreGroup::make()->asAction(Group::factory()->definition());
        $tenant = StoreTenant::make()->action($group, Tenant::factory()->definition());
    }
    $tenant->makeCurrent();
});

test('create shop', function () {
    $shop = StoreShop::make()->action(Shop::factory()->definition());
    $this->assertModelExists($shop);
    expect($shop->paymentAccounts()->count())->toBe(1)
        ->and($shop->outboxes()->count())->toBe(count(OutboxTypeEnum::values()));


    return $shop;
});

test('create offer campaign', function ($shop) {
    $offerCampaign = StoreOfferCampaign::make()->action($shop, OfferCampaign::factory()->definition());
    $this->assertModelExists($offerCampaign);

    return $offerCampaign;
})->depends('create shop');

test('update offer campaign', function ($offerCampaign) {
    $offerCampaign = UpdateOfferCampaign::make()->action($offerCampaign, OfferCampaign::factory()->definition());
    $this->assertModelExists($offerCampaign);
})->depends('create offer campaign');

test('create offer', function ($offerCampaign) {
    $offer = StoreOffer::make()->action($offerCampaign, Offer::factory()->definition());
    $this->assertModelExists($offer);

    return $offer;
})->depends('create offer campaign');

test('update offer', function ($offer) {
    $offer = UpdateOffer::make()->action($offer, Offer::factory()->definition());
    $this->assertModelExists($offer);
})->depends('create offer');

test('create offer component', function ($offerCampaign) {
    $offerComponent = StoreOfferComponent::make()->action($offerCampaign, OfferComponent::factory()->definition());
    $this->assertModelExists($offerComponent);

    return $offerComponent;
})->depends('create offer campaign');

test('update offer component', function ($offerComponent) {
    $offerComponent = UpdateOfferComponent::make()->action($offerComponent, OfferComponent::factory()->definition());
    $this->assertModelExists($offerComponent);
})->depends('create offer component');
