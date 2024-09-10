<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:03:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Catalogue\Shop\SeedOfferCampaigns;
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Discounts\Offer\StoreOffer;
use App\Actions\Discounts\Offer\UpdateOffer;
use App\Actions\Discounts\OfferCampaign\UpdateOfferCampaign;
use App\Actions\Discounts\OfferComponent\StoreOfferComponent;
use App\Actions\Discounts\OfferComponent\UpdateOfferComponent;
use App\Models\Catalogue\Shop;
use App\Models\Discounts\Offer;
use App\Models\Discounts\OfferCampaign;
use App\Models\Discounts\OfferComponent;

beforeAll(function () {
    loadDB();
});


beforeEach(function () {
    $this->organisation = createOrganisation();
});

test('create shop', function () {
    $shop = StoreShop::make()->action($this->organisation, Shop::factory()->definition());

    expect($shop)->toBeInstanceOf(Shop::class);

    return $shop;
});


test('seed offer campaigns', function ($shop) {
    SeedOfferCampaigns::run($shop);
    $this->artisan('shop:seed-offer-campaigns', [
        'shop' => $shop->slug,
    ])->assertSuccessful();
    expect($shop->offerCampaigns()->count())->toBe(7);
})->depends('create shop');

test('update offer campaign', function (Shop $shop) {
    $offerCampaign = $shop->offerCampaigns()->first();
    $offerCampaign = UpdateOfferCampaign::make()->action($offerCampaign, OfferCampaign::factory()->definition());
    $this->assertModelExists($offerCampaign);
})->depends('create shop');

test('create offer', function (Shop $shop) {
    $offerCampaign = $shop->offerCampaigns()->first();
    $offer         = StoreOffer::make()->action($offerCampaign, Offer::factory()->definition());
    $this->assertModelExists($offer);

    return $offer;
})->depends('create shop');

test('update offer', function ($offer) {
    $offer = UpdateOffer::make()->action($offer, Offer::factory()->definition());
    $this->assertModelExists($offer);
})->depends('create offer');

test('create offer component', function (Shop $shop) {
    $offerCampaign  = $shop->offerCampaigns()->first();
    $offerComponent = StoreOfferComponent::make()->action($offerCampaign, OfferComponent::factory()->definition());
    $this->assertModelExists($offerComponent);

    return $offerComponent;
})->depends('create shop');

test('update offer component', function ($offerComponent) {
    $offerComponent = UpdateOfferComponent::make()->action($offerComponent, OfferComponent::factory()->definition());
    $this->assertModelExists($offerComponent);
})->depends('create offer component');
