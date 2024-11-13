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
use App\Models\Discounts\OfferComponent;

beforeAll(function () {
    loadDB();
});


beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->group        = $this->organisation->group;
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

    $this->group->refresh();
    $this->organisation->refresh();

    expect($this->group->discountsStats->number_offer_campaigns)->toBe(7)
        ->and($this->group->discountsStats->number_current_offer_campaigns)->toBe(0)
        ->and($this->group->discountsStats->number_offer_campaigns_state_in_process)->toBe(7)
        ->and($this->organisation->discountsStats->number_offer_campaigns)->toBe(7)
        ->and($this->organisation->discountsStats->number_current_offer_campaigns)->toBe(0)
        ->and($this->organisation->discountsStats->number_offer_campaigns_state_in_process)->toBe(7)
        ->and($shop->discountsStats->number_offer_campaigns)->toBe(7)
        ->and($shop->discountsStats->number_current_offer_campaigns)->toBe(0)
        ->and($shop->discountsStats->number_offer_campaigns_state_in_process)->toBe(7);
})->depends('create shop');

test('update offer campaign', function (Shop $shop) {
    $offerCampaign = $shop->offerCampaigns()->first();
    $offerCampaign = UpdateOfferCampaign::make()->action($offerCampaign, [
        'name' => 'New Name',
    ]);
    expect($offerCampaign->name)->toBe('New Name');
})->depends('create shop');

test('create offer', function (Shop $shop) {
    $offerCampaign = $shop->offerCampaigns()->first();
    $offer         = StoreOffer::make()->action($offerCampaign, $shop, Offer::factory()->definition());
    $offerCampaign->refresh();
    $this->group->refresh();
    $this->organisation->refresh();

    expect($offer)->toBeInstanceOf(Offer::class)
        ->and($offerCampaign->stats->number_offers)->toBe(1)
        ->and($this->group->discountsStats->number_offers)->toBe(1)
        ->and($this->organisation->discountsStats->number_offers)->toBe(1)
        ->and($offerCampaign->shop->discountsStats->number_offers)->toBe(1);

    return $offer;
})->depends('create shop');

test('update offer', function ($offer) {
    $offer = UpdateOffer::make()->action($offer, ['name' => 'New Name A']);
    expect($offer->name)->toBe('New Name A');
})->depends('create offer');

test('create offer component', function (Offer $offer) {
    $offerComponent = StoreOfferComponent::make()->action($offer, $offer->shop, OfferComponent::factory()->definition());
    $this->assertModelExists($offerComponent);

    return $offerComponent;
})->depends('create offer');

test('update offer component', function ($offerComponent) {
    $offerComponent = UpdateOfferComponent::make()->action($offerComponent, OfferComponent::factory()->definition());
    $this->assertModelExists($offerComponent);
})->depends('create offer component');
