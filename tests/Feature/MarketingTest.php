<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 08:09:16 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Goods\TradeUnit\UpdateTradeUnit;
use App\Actions\Marketing\Offer\StoreOffer;
use App\Actions\Marketing\Offer\UpdateOffer;
use App\Actions\Marketing\OfferCampaign\StoreOfferCampaign;
use App\Actions\Marketing\OfferCampaign\UpdateOfferCampaign;
use App\Actions\Marketing\OfferComponent\StoreOfferComponent;
use App\Actions\Marketing\OfferComponent\UpdateOfferComponent;
use App\Actions\Marketing\Product\DeleteProduct;
use App\Actions\Marketing\Product\StoreProduct;
use App\Actions\Marketing\Product\UpdateProduct;
use App\Actions\Marketing\ProductCategory\StoreProductCategory;
use App\Actions\Marketing\ProductCategory\UpdateProductCategory;
use App\Actions\Marketing\ShippingZone\StoreShippingZone;
use App\Actions\Marketing\ShippingZone\UpdateShippingZone;
use App\Actions\Marketing\ShippingZoneSchema\StoreShippingZoneSchema;
use App\Actions\Marketing\ShippingZoneSchema\UpdateShippingZoneSchema;
use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Marketing\Shop\UpdateShop;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Marketing\Offer;
use App\Models\Marketing\OfferCampaign;
use App\Models\Marketing\OfferComponent;
use App\Models\Marketing\Product;
use App\Models\Marketing\ProductCategory;
use App\Models\Marketing\ShippingZone;
use App\Models\Marketing\ShippingZoneSchema;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
});

test('create shop', function () {
    $shop = StoreShop::make()->action(Shop::factory()->definition());
    $this->assertModelExists($shop);
    expect($shop->paymentAccounts()->count())->toBe(1)
        ->and($shop->outboxes()->count())->toBe(count(OutboxTypeEnum::values()));


    return $shop;
});

test('update shop', function ($shop) {
    $shop = UpdateShop::make()->action($shop, Shop::factory()->definition());

    $this->assertModelExists($shop);
})->depends('create shop');

test('create product category', function ($shop) {
    $productCategory = StoreProductCategory::make()->action($shop, ProductCategory::factory()->definition());
    $this->assertModelExists($productCategory);

    return $productCategory;
})->depends('create shop');

test('create sub product category', function ($productCategory) {
    $subProductCategory = StoreProductCategory::make()->action($productCategory, ProductCategory::factory()->definition());
    $this->assertModelExists($subProductCategory);

    return $subProductCategory;
})->depends('create product category');

test('create product category 2', function ($shop) {
    $productCategory = StoreProductCategory::make()->action($shop, ProductCategory::factory()->definition());
    $this->assertModelExists($productCategory);

    return $productCategory;
})->depends('create shop');

test('get list of the parents', function ($productCategory) {
    //
})->depends('create product category')->todo();

test('update product category', function ($productCategory) {
    $productCategory = UpdateProductCategory::make()->action($productCategory, ProductCategory::factory()->definition());

    $this->assertModelExists($productCategory);
})->depends('create product category');

test('create product', function ($shop) {
    $product = StoreProduct::make()->action($shop, Product::factory()->definition());
    $this->assertModelExists($product);

    return $product;
})->depends('create shop');

test('update product', function ($product) {
    $product = UpdateProduct::make()->action($product, Product::factory()->definition());

    $this->assertModelExists($product);
})->depends('create product');

test('delete product', function ($product) {
    $product = DeleteProduct::run($product);

    $this->assertModelExists($product);
})->depends('create product');

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

test('create shipping zone schema', function ($shop) {
    $shippingZoneSchema = StoreShippingZoneSchema::make()->action($shop, ShippingZoneSchema::factory()->definition());
    $this->assertModelExists($shop);

    return $shippingZoneSchema;
})->depends('create shop');

test('update shipping zone schema', function ($shippingZoneSchema) {
    $shippingZoneSchema = UpdateShippingZoneSchema::make()->action($shippingZoneSchema, ShippingZoneSchema::factory()->definition());
    $this->assertModelExists($shippingZoneSchema);
})->depends('create shipping zone schema');

test('create shipping zone', function ($shippingZoneSchema) {
    $shippingZone = StoreShippingZone::make()->action($shippingZoneSchema, ShippingZone::factory()->definition());
    $this->assertModelExists($shippingZoneSchema);

    return $shippingZone;
})->depends('create shipping zone schema');

test('update shipping zone', function ($shippingZone) {
    $shippingZone = UpdateShippingZone::make()->action($shippingZone, ShippingZone::factory()->definition());
    $this->assertModelExists($shippingZone);
})->depends('create shipping zone');

test('create trade unit', function () {
    $tradeUnit = StoreTradeUnit::make()->action(TradeUnit::factory()->definition());
    $this->assertModelExists($tradeUnit);

    return $tradeUnit;
});

test('update trade unit', function ($tradeUnit) {
    $tradeUnit = UpdateTradeUnit::make()->action($tradeUnit, TradeUnit::factory()->definition());
    $this->assertModelExists($tradeUnit);
})->depends('create trade unit');
